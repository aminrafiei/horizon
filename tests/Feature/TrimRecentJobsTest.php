<?php

namespace Aminrafiei\Horizon\Tests\Feature;

use Mockery;
use Cake\Chronos\Chronos;
use Aminrafiei\Horizon\MasterSupervisor;
use Aminrafiei\Horizon\Tests\IntegrationTest;
use Aminrafiei\Horizon\Contracts\JobRepository;
use Aminrafiei\Horizon\Listeners\TrimRecentJobs;
use Aminrafiei\Horizon\Events\MasterSupervisorLooped;

class TrimRecentJobsTest extends IntegrationTest
{
    public function test_trimmer_has_a_cooldown_period()
    {
        $trim = new TrimRecentJobs;

        $repository = Mockery::mock(JobRepository::class);
        $repository->shouldReceive('trimRecentJobs')->twice();
        $this->app->instance(JobRepository::class, $repository);

        // Should not be called first time since date is initialized...
        $trim->handle(new MasterSupervisorLooped(Mockery::mock(MasterSupervisor::class)));

        Chronos::setTestNow(Chronos::now()->addMinutes(30));

        // Should only be called twice...
        $trim->handle(new MasterSupervisorLooped(Mockery::mock(MasterSupervisor::class)));
        $trim->handle(new MasterSupervisorLooped(Mockery::mock(MasterSupervisor::class)));
        $trim->handle(new MasterSupervisorLooped(Mockery::mock(MasterSupervisor::class)));

        Chronos::setTestNow();
    }
}
