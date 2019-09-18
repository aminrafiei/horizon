<?php

namespace Aminrafiei\Horizon\Listeners;

use Aminrafiei\Horizon\Events\JobFailed;
use Aminrafiei\Horizon\Contracts\JobRepository;

class MarkJobAsFailed
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
     * @param  \Aminrafiei\Horizon\Events\JobFailed  $event
     * @return void
     */
    public function handle(JobFailed $event)
    {
        $this->jobs->failed(
            $event->exception, $event->connectionName,
            $event->queue, $event->payload
        );
    }
}
