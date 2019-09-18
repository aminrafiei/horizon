<?php

namespace Aminrafiei\Horizon\Listeners;

use Aminrafiei\Horizon\Events\SupervisorLooped;

class MonitorSupervisorMemory
{
    /**
     * Handle the event.
     *
     * @param  \Aminrafiei\Horizon\Events\SupervisorLooped  $event
     * @return void
     */
    public function handle(SupervisorLooped $event)
    {
        $supervisor = $event->supervisor;

        if ($supervisor->memoryUsage() > $supervisor->options->memory) {
            $supervisor->terminate(12);
        }
    }
}
