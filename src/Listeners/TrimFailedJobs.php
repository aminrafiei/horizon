<?php

namespace Aminrafiei\Horizon\Listeners;

use Cake\Chronos\Chronos;
use Aminrafiei\Horizon\Contracts\JobRepository;
use Aminrafiei\Horizon\Events\MasterSupervisorLooped;

class TrimFailedJobs
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
    public $frequency = 5040;

    /**
     * Handle the event.
     *
     * @param  \Aminrafiei\Horizon\Events\MasterSupervisorLooped  $event
     * @return void
     */
    public function handle(MasterSupervisorLooped $event)
    {
        if (! isset($this->lastTrimmed)) {
            $this->frequency = max(1, intdiv(
                config('horizon.trim.failed', 10080), 12
            ));

            $this->lastTrimmed = Chronos::now()->subMinutes($this->frequency + 1);
        }

        if ($this->lastTrimmed->lte(Chronos::now()->subMinutes($this->frequency))) {
            app(JobRepository::class)->trimFailedJobs();

            $this->lastTrimmed = Chronos::now();
        }
    }
}
