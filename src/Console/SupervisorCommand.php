<?php

namespace Aminrafiei\Horizon\Console;

use Exception;
use Illuminate\Console\Command;
use Aminrafiei\Horizon\SupervisorFactory;
use Aminrafiei\Horizon\SupervisorOptions;

class SupervisorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'horizon:supervisor
                            {name : The name of supervisor}
                            {connection : The name of the connection to work}
                            {--balance= : The balancing strategy the supervisor should apply}
                            {--delay=0 : Amount of time to delay failed jobs}
                            {--force : Force the worker to run even in maintenance mode}
                            {--max-processes=1 : The maximum number of total workers to start}
                            {--min-processes=1 : The minimum number of workers to assign per queue}
                            {--memory=128 : The memory limit in megabytes}
                            {--paused : Start the supervisor in a paused state}
                            {--queue= : The names of the queues to work}
                            {--sleep=3 : Number of seconds to sleep when no job is available}
                            {--timeout=60 : The number of seconds a child process can run}
                            {--tries=0 : Number of times to attempt a job before logging it failed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start a new supervisor';

    /**
     * Indicates whether the command should be shown in the Artisan command list.
     *
     * @var bool
     */
    protected $hidden = true;

    /**
     * Execute the console command.
     *
     * @param  \Aminrafiei\Horizon\SupervisorFactory  $factory
     * @return int
     */
    public function handle(SupervisorFactory $factory)
    {
        $supervisor = $factory->make(
            $this->supervisorOptions()
        );

        try {
            $supervisor->ensureNoDuplicateSupervisors();
        } catch (Exception $e) {
            $this->error('A supervisor with this name is already running.');

            return 13;
        }

        $this->start($supervisor);
    }

    /**
     * Start the given supervisor.
     *
     * @param  \Aminrafiei\Horizon\Supervisor  $supervisor
     * @return void
     */
    protected function start($supervisor)
    {
        $supervisor->handleOutputUsing(function ($type, $line) {
            $this->output->write($line);
        });

        $supervisor->working = ! $this->option('paused');

        $supervisor->scale(max(
            0, $this->option('max-processes') - $supervisor->totalSystemProcessCount()
        ));

        $supervisor->monitor();
    }

    /**
     * Get the supervisor options.
     *
     * @return \Aminrafiei\Horizon\SupervisorOptions
     */
    protected function supervisorOptions()
    {
        return new SupervisorOptions(
            $this->argument('name'),
            $this->argument('connection'),
            $this->getQueue($this->argument('connection')),
            $this->option('balance'),
            $this->option('delay'),
            $this->option('max-processes'),
            $this->option('min-processes'),
            $this->option('memory'),
            $this->option('timeout'),
            $this->option('sleep'),
            $this->option('tries'),
            $this->option('force')
        );
    }

    /**
     * Get the queue name for the worker.
     *
     * @param  string  $connection
     * @return string
     */
    protected function getQueue($connection)
    {
        return $this->option('queue') ?: $this->laravel['config']->get(
            "queue.connections.{$connection}.queue", 'default'
        );
    }
}
