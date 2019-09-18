<?php

namespace Aminrafiei\Horizon\Tests\Feature;

use Aminrafiei\Horizon\SupervisorFactory;
use Aminrafiei\Horizon\Tests\IntegrationTest;
use Aminrafiei\Horizon\Tests\Feature\Fixtures\FakeSupervisorFactory;

class SupervisorCommandTest extends IntegrationTest
{
    public function test_supervisor_command_can_start_supervisor_monitoring()
    {
        $this->app->instance(SupervisorFactory::class, $factory = new FakeSupervisorFactory);
        $this->artisan('horizon:supervisor', ['name' => 'foo', 'connection' => 'redis']);

        $this->assertTrue($factory->supervisor->monitoring);
        $this->assertTrue($factory->supervisor->working);
    }

    public function test_supervisor_command_can_start_paused_supervisors()
    {
        $this->app->instance(SupervisorFactory::class, $factory = new FakeSupervisorFactory);
        $this->artisan('horizon:supervisor', ['name' => 'foo', 'connection' => 'redis', '--paused' => true]);

        $this->assertFalse($factory->supervisor->working);
    }
}
