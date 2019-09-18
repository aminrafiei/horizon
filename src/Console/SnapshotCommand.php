<?php

namespace Aminrafiei\Horizon\Console;

use Aminrafiei\Horizon\Lock;
use Illuminate\Console\Command;
use Aminrafiei\Horizon\Contracts\MetricsRepository;

class SnapshotCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'horizon:snapshot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Store a snapshot of the queue metrics';

    /**
     * Execute the console command.
     *
     * @param  \Aminrafiei\Horizon\Lock  $lock
     * @param  \Aminrafiei\Horizon\Contracts\MetricsRepository  $metrics
     * @return void
     */
    public function handle(Lock $lock, MetricsRepository $metrics)
    {
        if ($lock->get('metrics:snapshot', 300)) {
            $metrics->snapshot();

            $this->info('Metrics snapshot stored successfully.');
        }
    }
}
