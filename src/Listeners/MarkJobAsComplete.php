<?php

namespace Aminrafiei\Horizon\Listeners;

use Aminrafiei\Horizon\Events\JobDeleted;
use Aminrafiei\Horizon\Contracts\JobRepository;
use Aminrafiei\Horizon\Contracts\TagRepository;

class MarkJobAsComplete
{
    /**
     * The job repository implementation.
     *
     * @var \Aminrafiei\Horizon\Contracts\JobRepository
     */
    public $jobs;

    /**
     * The tag repository implementation.
     *
     * @var \Aminrafiei\Horizon\Contracts\TagRepository
     */
    public $tags;

    /**
     * Create a new listener instance.
     *
     * @param  \Aminrafiei\Horizon\Contracts\JobRepository  $jobs
     * @param  \Aminrafiei\Horizon\Contracts\TagRepository  $tags
     * @return void
     */
    public function __construct(JobRepository $jobs, TagRepository $tags)
    {
        $this->jobs = $jobs;
        $this->tags = $tags;
    }

    /**
     * Handle the event.
     *
     * @param  \Aminrafiei\Horizon\Events\JobDeleted  $event
     * @return void
     */
    public function handle(JobDeleted $event)
    {
        $this->jobs->completed($event->payload, $event->job->hasFailed());

        if (! $event->job->hasFailed() && count($this->tags->monitored($event->payload->tags())) > 0) {
            $this->jobs->remember($event->connectionName, $event->queue, $event->payload);
        }
    }
}
