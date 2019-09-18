<?php

namespace Aminrafiei\Horizon\Listeners;

use Aminrafiei\Horizon\Events\JobReleased;
use Aminrafiei\Horizon\Contracts\JobRepository;

class MarkJobAsReleased
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
     * @param  \Aminrafiei\Horizon\Events\JobReleased  $event
     * @return void
     */
    public function handle(JobReleased $event)
    {
        $this->jobs->released($event->connectionName, $event->queue, $event->payload);
    }
}
