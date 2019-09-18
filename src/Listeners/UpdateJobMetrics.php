<?php

namespace Aminrafiei\Horizon\Listeners;

use Aminrafiei\Horizon\Stopwatch;
use Aminrafiei\Horizon\Events\JobDeleted;
use Aminrafiei\Horizon\Contracts\MetricsRepository;

class UpdateJobMetrics
{
    /**
     * The metrics repository implementation.
     *
     * @var \Aminrafiei\Horizon\Contracts\MetricsRepository
     */
    public $metrics;

    /**
     * The stopwatch instance.
     *
     * @var \Aminrafiei\Horizon\Stopwatch
     */
    public $watch;

    /**
     * Create a new listener instance.
     *
     * @param  \Aminrafiei\Horizon\Contracts\MetricsRepository  $metrics
     * @param  \Aminrafiei\Horizon\Stopwatch  $watch
     * @return void
     */
    public function __construct(MetricsRepository $metrics, Stopwatch $watch)
    {
        $this->watch = $watch;
        $this->metrics = $metrics;
    }

    /**
     * Stop gathering metrics for a job.
     *
     * @param  \Aminrafiei\Horizon\Events\JobDeleted  $event
     * @return void
     */
    public function handle(JobDeleted $event)
    {
        if ($event->job->hasFailed()) {
            return;
        }

        $time = $this->watch->check($event->payload->id());

        $this->metrics->incrementQueue(
            $event->job->getQueue(), $time
        );

        $this->metrics->incrementJob(
            $event->payload->displayName(), $time
        );
    }
}
