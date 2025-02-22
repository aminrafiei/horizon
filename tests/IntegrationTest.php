<?php

namespace Aminrafiei\Horizon\Tests;

use Aminrafiei\Horizon\Horizon;
use Orchestra\Testbench\TestCase;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Support\Facades\Redis;
use Aminrafiei\Horizon\WorkerCommandString;
use Aminrafiei\Horizon\Contracts\JobRepository;
use Aminrafiei\Horizon\Contracts\TagRepository;
use Aminrafiei\Horizon\SupervisorCommandString;

abstract class IntegrationTest extends TestCase
{
    /**
     * Setup the test case.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Redis::flushall();
    }

    /**
     * Tear down the test case.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        Redis::flushall();
        WorkerCommandString::reset();
        SupervisorCommandString::reset();
        Horizon::$authUsing = null;

        parent::tearDown();
    }

    /**
     * Run the given assertion callback with a retry loop.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public function wait($callback)
    {
        retry(10, $callback, 1000);
    }

    /**
     * Get the total number of recent jobs.
     *
     * @return int
     */
    protected function recentJobs()
    {
        return app(JobRepository::class)->totalRecent();
    }

    /**
     * Get the total number of monitored jobs for a given tag.
     *
     * @param  string  $tag
     * @return int
     */
    protected function monitoredJobs($tag)
    {
        return app(TagRepository::class)->count($tag);
    }

    /**
     * Get the total number of failed jobs.
     *
     * @return int
     */
    protected function failedJobs()
    {
        return app(JobRepository::class)->totalFailed();
    }

    /**
     * Run the next job on the queue.
     *
     * @param  int  $times
     * @return void
     */
    protected function work($times = 1)
    {
        for ($i = 0; $i < $times; $i++) {
            $this->worker()->runNextJob(
                'redis', 'default', $this->workerOptions()
            );
        }
    }

    /**
     * Get the queue worker instance.
     *
     * @return \Illuminate\Queue\Worker
     */
    protected function worker()
    {
        return app('queue.worker');
    }

    /**
     * Get the options for the worker.
     *
     * @return \Illuminate\Queue\WorkerOptions
     */
    protected function workerOptions()
    {
        return tap(new WorkerOptions, function ($options) {
            $options->sleep = 0;
            $options->maxTries = 1;
        });
    }

    /**
     * Get the service providers for the package.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return ['Aminrafiei\Horizon\HorizonServiceProvider'];
    }

    /**
     * Configure the environment.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('queue.default', 'redis');
    }
}
