<?php

namespace Aminrafiei\Horizon\Listeners;

use Aminrafiei\Horizon\Events\JobPushed;
use Aminrafiei\Horizon\Contracts\JobRepository;

class StoreJob
{
    /**
     * The job repository implementation.
     *
     * @var \Aminrafiei\Horizon\Contracts\JobRepository
     */
    public $jobs;

    /**
     * Create a new listener instance.
     *
     * @param  \Aminrafiei\Horizon\Contracts\JobRepository  $jobs
     * @return void
     */
    public function __construct(JobRepository $jobs)
    {
        $this->jobs = $jobs;
    }

    /**
     * Handle the event.
     *
     * @param  \Aminrafiei\Horizon\Events\JobPushed  $event
     * @return void
     */
    public function handle(JobPushed $event)
    {
        $this->jobs->pushed(
            $event->connectionName, $event->queue, $event->payload
        );
    }
}
