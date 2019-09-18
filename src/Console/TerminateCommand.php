<?php

namespace Aminrafiei\Horizon\Console;

use Carbon\Carbon;
use DateInterval;
use DateTimeInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Aminrafiei\Horizon\MasterSupervisor;
use Illuminate\Support\InteractsWithTime;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Aminrafiei\Horizon\Contracts\MasterSupervisorRepository;

class TerminateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'horizon:terminate
                            {--wait : Wait for all workers to terminate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Terminate the master supervisor so it can be restarted';

    /**
     * Execute the console command.
     *
     * @param  \Illuminate\Contracts\Cache\Factory $cache
     * @param  \Aminrafiei\Horizon\Contracts\MasterSupervisorRepository  $masters
     * @return void
     */
    public function handle(CacheFactory $cache, MasterSupervisorRepository $masters)
    {
        if (config('horizon.fast_termination')) {
            $cache->forever(
                'horizon:terminate:wait', $this->option('wait')
            );
        }

        $masters = collect($masters->all())->filter(function ($master) {
            return Str::startsWith($master->name, MasterSupervisor::basename());
        })->all();

        foreach (Arr::pluck($masters, 'pid') as $processId) {
            $this->info("Sending TERM Signal To Process: {$processId}");

            if (! posix_kill($processId, SIGTERM)) {
                $this->error("Failed to kill process: {$processId} (".posix_strerror(posix_get_last_error()).')');
            }
        }

        $this->laravel['cache']->forever('illuminate:queue:restart', $this->currentTime());
    }

    /**
     * Get the number of seconds until the given DateTime.
     *
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @return int
     */
    protected function secondsUntil($delay)
    {
        $delay = $this->parseDateInterval($delay);
        return $delay instanceof DateTimeInterface
            ? max(0, $delay->getTimestamp() - $this->currentTime())
            : (int) $delay;
    }
    /**
     * Get the "available at" UNIX timestamp.
     *
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @return int
     */
    protected function availableAt($delay = 0)
    {
        $delay = $this->parseDateInterval($delay);
        return $delay instanceof DateTimeInterface
            ? $delay->getTimestamp()
            : Carbon::now()->addSeconds($delay)->getTimestamp();
    }
    /**
     * If the given value is an interval, convert it to a DateTime instance.
     *
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @return \DateTimeInterface|int
     */
    protected function parseDateInterval($delay)
    {
        if ($delay instanceof DateInterval) {
            $delay = Carbon::now()->add($delay);
        }
        return $delay;
    }
    /**
     * Get the current system time as a UNIX timestamp.
     *
     * @return int
     */
    protected function currentTime()
    {
        return Carbon::now()->getTimestamp();
    }
}
