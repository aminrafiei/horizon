<?php

namespace Aminrafiei\Horizon\Listeners;

use Aminrafiei\Horizon\Events\MasterSupervisorLooped;
use Aminrafiei\Horizon\Contracts\SupervisorRepository;
use Aminrafiei\Horizon\Contracts\MasterSupervisorRepository;

class ExpireSupervisors
{
    /**
     * Handle the event.
     *
     * @param  \Aminrafiei\Horizon\Events\MasterSupervisorLooped  $event
     * @return void
     */
    public function handle(MasterSupervisorLooped $event)
    {
        app(MasterSupervisorRepository::class)->flushExpired();

        app(SupervisorRepository::class)->flushExpired();
    }
}
