<?php

namespace Aminrafiei\Horizon\Listeners;

use Cake\Chronos\Chronos;
use Aminrafiei\Horizon\Contracts\JobRepository;
use Aminrafiei\Horizon\Events\MasterSupervisorLooped;

class TrimRecentJobs
{
    /**
     * The last time the recent jobs were trimmed.
     *
     * @var \Cake\Chronos\Chronos
     */
    public $lastTrimmed;

    /**
     * How many minutes to wait in between each trim.
     *
     * @var int
     */
    public $frequency = 1;

    /**
     * Handle the event.
     *
     * @param  \Aminrafiei\Horizon\Events\MasterSupervisorLooped  $event
     * @return void
     */
    public function handle(MasterSupervisorLooped $event)
    {
        if (! isset($this->lastTrimmed)) {
            $this->lastTrimmed = Chronos::now()->subMinutes($this->frequency + 1);
        }

        if ($this->lastTrimmed->lte(Chronos::now()->subMinutes($this->frequency))) {
            app(JobRepository::class)->trimRecentJobs();

            $this->lastTrimmed = Chronos::now();
        }
    }
}
