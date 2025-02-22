<?php

namespace Aminrafiei\Horizon\Tests\Controller;

use Mockery;
use Aminrafiei\Horizon\WaitTimeCalculator;
use Aminrafiei\Horizon\Contracts\JobRepository;
use Aminrafiei\Horizon\Contracts\MetricsRepository;
use Aminrafiei\Horizon\Contracts\SupervisorRepository;
use Aminrafiei\Horizon\Contracts\MasterSupervisorRepository;

class DashboardStatsControllerTest extends AbstractControllerTest
{
    public function test_all_stats_are_correctly_returned()
    {
        // Setup supervisor data...
        $supervisors = Mockery::mock(SupervisorRepository::class);
        $supervisors->shouldReceive('all')->andReturn([
            (object) [
                'processes' => [
                    'redis:first' => 10,
                    'redis:second' => 10,
                ],
            ],
            (object) [
                'processes' => [
                    'redis:first' => 10,
                ],
            ],
        ]);
        $this->app->instance(SupervisorRepository::class, $supervisors);

        // Setup metrics data...
        $metrics = Mockery::mock(MetricsRepository::class);
        $metrics->shouldReceive('jobsProcessedPerMinute')->andReturn(1);
        $metrics->shouldReceive('queueWithMaximumRuntime')->andReturn('default');
        $metrics->shouldReceive('queueWithMaximumThroughput')->andReturn('default');
        $this->app->instance(MetricsRepository::class, $metrics);

        $jobs = Mockery::mock(JobRepository::class);
        $jobs->shouldReceive('countRecentlyFailed')->andReturn(1);
        $jobs->shouldReceive('countRecent')->andReturn(1);
        $this->app->instance(JobRepository::class, $jobs);

        // Setup wait time data...
        $wait = Mockery::mock(WaitTimeCalculator::class);
        $wait->shouldReceive('calculate')->andReturn([
            'first' => 20,
            'second' => 10,
        ]);
        $this->app->instance(WaitTimeCalculator::class, $wait);

        $response = $this->actingAs(new Fakes\User)
                    ->get('/horizon/api/stats');

        $response->assertJson([
            'jobsPerMinute' => 1,
            'wait' => ['first' => 20],
            'processes' => 30,
            'status' => 'inactive',
            'recentlyFailed' => 1,
            'recentJobs' => 1,
            'queueWithMaxRuntime' => 'default',
            'queueWithMaxThroughput' => 'default',
            'periods' => [
                'recentJobs' => 60,
                'recentlyFailed' => 10080,
            ],
        ]);
    }

    public function test_paused_status_is_reflected_if_all_master_supervisors_are_paused()
    {
        $masters = Mockery::mock(MasterSupervisorRepository::class);
        $masters->shouldReceive('all')->andReturn([
            (object) [
                'status' => 'running',
            ],
            (object) [
                'status' => 'paused',
            ],
        ]);
        $this->app->instance(MasterSupervisorRepository::class, $masters);

        $response = $this->actingAs(new Fakes\User)
                    ->get('/horizon/api/stats');

        $response->assertJson([
            'status' => 'paused',
        ]);
    }
}
