<?php

namespace Aminrafiei\Horizon\Tests\Feature;

use Mockery;
use Aminrafiei\Horizon\PhpBinary;
use Illuminate\Support\Facades\Redis;
use Aminrafiei\Horizon\MasterSupervisor;
use Aminrafiei\Horizon\SupervisorOptions;
use Aminrafiei\Horizon\SupervisorProcess;
use Aminrafiei\Horizon\WorkerCommandString;
use Aminrafiei\Horizon\Tests\IntegrationTest;
use Aminrafiei\Horizon\Contracts\HorizonCommandQueue;
use Aminrafiei\Horizon\Contracts\MasterSupervisorRepository;
use Aminrafiei\Horizon\MasterSupervisorCommands\AddSupervisor;
use Aminrafiei\Horizon\Tests\Feature\Fixtures\EternalSupervisor;
use Aminrafiei\Horizon\Tests\Feature\Fixtures\SupervisorProcessWithFakeRestart;

class MasterSupervisorTest extends IntegrationTest
{
    public function test_names_can_be_customized()
    {
        MasterSupervisor::determineNameUsing(function () {
            return 'test-name';
        });

        $master = new MasterSupervisor;

        $this->assertStringStartsWith('test-name', $master->name);
        $this->assertStringStartsWith('test-name', $master->name());
        $this->assertStringStartsWith('test-name', $master->name());

        MasterSupervisor::$nameResolver = null;
    }

    public function test_master_process_marks_clean_exits_as_dead_and_removes_them()
    {
        $master = new MasterSupervisor;
        $master->working = true;
        $master->supervisors[] = $supervisorProcess = new SupervisorProcess($this->options(), $process = Mockery::mock());

        $process->shouldReceive('isStarted')->andReturn(true);
        $process->shouldReceive('isRunning')->andReturn(false);
        $process->shouldReceive('getExitCode')->andReturn(0);

        $master->loop();

        $this->assertTrue($supervisorProcess->dead);
        $this->assertCount(0, $master->supervisors);
    }

    public function test_master_process_marks_duplicates_as_dead_and_removes_them()
    {
        $master = new MasterSupervisor;
        $master->working = true;
        $master->supervisors[] = $supervisorProcess = new SupervisorProcess($this->options(), $process = Mockery::mock());

        $process->shouldReceive('isStarted')->andReturn(true);
        $process->shouldReceive('isRunning')->andReturn(false);
        $process->shouldReceive('getExitCode')->andReturn(13);

        $master->loop();

        $this->assertTrue($supervisorProcess->dead);
        $this->assertCount(0, $master->supervisors);
    }

    public function test_master_process_restarts_unexpected_exits()
    {
        $master = new MasterSupervisor;
        $master->working = true;
        $master->supervisors[] = $supervisorProcess = new SupervisorProcessWithFakeRestart($this->options(), $process = Mockery::mock());

        $process->shouldReceive('isStarted')->andReturn(true);
        $process->shouldReceive('isRunning')->andReturn(false);
        $process->shouldReceive('getExitCode')->andReturn(50);

        $master->loop();

        $this->assertTrue($supervisorProcess->dead);
        $commands = Redis::connection('horizon')->lrange(
            'commands:'.MasterSupervisor::commandQueueFor(MasterSupervisor::name()), 0, -1
        );

        $this->assertCount(1, $commands);
        $command = (object) json_decode($commands[0], true);

        $this->assertCount(0, $master->supervisors);
        $this->assertEquals(AddSupervisor::class, $command->command);
        $this->assertSame('default', $command->options['queue']);
    }

    public function test_master_process_restarts_processes_that_never_started()
    {
        $master = new MasterSupervisor;
        $master->working = true;
        $master->supervisors[] = $supervisorProcess = new SupervisorProcessWithFakeRestart($this->options(), $process = Mockery::mock());

        $process->shouldReceive('isStarted')->andReturn(false);

        $master->loop();

        $this->assertFalse($supervisorProcess->dead);
        $this->assertCount(1, $master->supervisors);
        $this->assertTrue($supervisorProcess->wasRestarted);
    }

    public function test_master_process_starts_unstarted_processes_when_unpaused()
    {
        $master = new MasterSupervisor;
        $master->supervisors[] = $supervisorProcess = new SupervisorProcessWithFakeRestart($this->options(), $process = Mockery::mock());

        $process->shouldReceive('isStarted')->andReturn(false);
        $process->shouldReceive('isRunning')->andReturn(false);

        $master->loop();

        $this->assertFalse($supervisorProcess->dead);
        $this->assertCount(1, $master->supervisors);
        $this->assertTrue($supervisorProcess->wasRestarted);
    }

    public function test_master_process_loop_processes_pending_commands()
    {
        $this->app->singleton(Commands\FakeMasterCommand::class);

        $master = new MasterSupervisor;
        $master->working = true;

        resolve(HorizonCommandQueue::class)->push(
            $master->commandQueue(), Commands\FakeMasterCommand::class, ['foo' => 'bar']
        );

        // Loop twice to make sure command is only called once...
        $master->loop();
        $master->loop();

        $command = resolve(Commands\FakeMasterCommand::class);

        $this->assertEquals(1, $command->processCount);
        $this->assertEquals($master, $command->master);
        $this->assertEquals(['foo' => 'bar'], $command->options);
    }

    public function test_master_process_information_is_persisted()
    {
        $master = new MasterSupervisor;
        $master->working = true;
        $master->supervisors[] = new SupervisorProcess($this->options(), $process = Mockery::mock());
        $process->shouldReceive('isStarted')->andReturn(true);
        $process->shouldReceive('isRunning')->andReturn(true);
        $process->shouldReceive('signal');

        $master->loop();

        $masterRecord = resolve(MasterSupervisorRepository::class)->find($master->name);

        $this->assertNotNull($masterRecord->pid);
        $this->assertEquals([MasterSupervisor::name().':name'], $masterRecord->supervisors);
        $this->assertSame('running', $masterRecord->status);

        $master->pause();
        $master->loop();

        $masterRecord = resolve(MasterSupervisorRepository::class)->find($master->name);
        $this->assertSame('paused', $masterRecord->status);
    }

    /**
     * @expectedException \Exception
     */
    public function test_master_process_should_not_allow_duplicate_master_process_on_same_machine()
    {
        $master = new MasterSupervisor;
        $master->working = true;
        $master2 = new MasterSupervisor;
        $master2->working = true;

        $master->persist();
        $master->monitor();
    }

    public function test_supervisor_repository_returns_null_if_no_supervisor_exists_with_given_name()
    {
        $repository = resolve(MasterSupervisorRepository::class);

        $this->assertNull($repository->find('nothing'));
    }

    public function test_supervisor_process_terminates_all_workers_and_exits_on_full_termination()
    {
        $master = new Fakes\MasterSupervisorWithFakeExit;
        $master->working = true;

        $repository = resolve(MasterSupervisorRepository::class);
        $repository->forgetDelay = 1;

        $master->persist();
        $master->terminate();

        $this->assertTrue($master->exited);

        // Assert that the supervisor is removed...
        $this->assertNull(resolve(MasterSupervisorRepository::class)->find($master->name));
    }

    public function test_supervisor_continues_termination_if_supervisors_take_too_long()
    {
        $master = new Fakes\MasterSupervisorWithFakeExit;
        $master->working = true;

        $repository = resolve(MasterSupervisorRepository::class);
        $repository->forgetDelay = 1;

        $master->supervisors = collect([new EternalSupervisor]);

        $master->persist();
        $master->terminate();

        $this->assertTrue($master->exited);
    }

    protected function options()
    {
        return tap(new SupervisorOptions(MasterSupervisor::name().':name', 'redis'), function ($options) {
            $phpBinary = PhpBinary::path();
            $options->directory = realpath(__DIR__.'/../');

            WorkerCommandString::$command = 'exec '.$phpBinary.' worker.php';
        });
    }
}
