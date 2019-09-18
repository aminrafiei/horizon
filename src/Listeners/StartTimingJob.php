<?php

namespace Aminrafiei\Horizon\Listeners;

use Aminrafiei\Horizon\Stopwatch;
use Aminrafiei\Horizon\Events\JobReserved;

class StartTimingJob
{
    /**
     * The stopwatch instance.
     *
     * @var \Aminrafiei\Horizon\Stopwatch
     */
    public $watch;

    /**
     * Create a new listener instance.
     *
     * @param  \Aminrafiei\Horizon\Stopwatch  $watch
     * @return void
     */
    public function __construct(Stopwatch $watch)
    {
        $this->watch = $watch;
    }

    /**
     * Handle the event.
     *
     * @param  \Aminrafiei\Horizon\Events\JobReserved  $event
     * @return void
     */
    public function handle(JobReserved $event)
    {
        $this->watch->start($event->payload->id());
    }
}
