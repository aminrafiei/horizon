<?php

namespace Aminrafiei\Horizon\Listeners;

use Aminrafiei\Horizon\Events\SupervisorLooped;

class PruneTerminatingProcesses
{
    /**
     * Handle the event.
     *
     * @param  \Aminrafiei\Horizon\Events\SupervisorLooped  $event
     * @return void
     */
    public function handle(SupervisorLooped $event)
    {
        $event->supervisor->pruneTerminatingProcesses();
    }
}
