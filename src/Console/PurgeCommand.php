<?php

namespace Aminrafiei\Horizon\Console;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Aminrafiei\Horizon\MasterSupervisor;
use Aminrafiei\Horizon\ProcessInspector;
use Aminrafiei\Horizon\Contracts\ProcessRepository;
use Aminrafiei\Horizon\Contracts\SupervisorRepository;
use Aminrafiei\Horizon\Contracts\MasterSupervisorRepository;

class PurgeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'horizon:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Terminate any rogue Horizon processes';

    /**
     * @var \Aminrafiei\Horizon\Contracts\SupervisorRepository
     */
    private $supervisors;

    /**
     * @var \Aminrafiei\Horizon\Contracts\ProcessRepository
     */
    private $processes;

    /**
     * @var \Aminrafiei\Horizon\ProcessInspector
     */
    private $inspector;

    /**
     * Create a new command instance.
     *
     * @param  \Aminrafiei\Horizon\Contracts\SupervisorRepository  $supervisors
     * @param  \Aminrafiei\Horizon\Contracts\ProcessRepository  $processes
     * @param  \Aminrafiei\Horizon\ProcessInspector  $inspector
     * @return void
     */
    public function __construct(
        SupervisorRepository $supervisors,
        ProcessRepository $processes,
        ProcessInspector $inspector
    ) {
        parent::__construct();

        $this->supervisors = $supervisors;
        $this->processes = $processes;
        $this->inspector = $inspector;
    }

    /**
     * Execute the console command.
     *
     * @param  \Aminrafiei\Horizon\Contracts\MasterSupervisorRepository  $masters
     * @return void
     */
    public function handle(MasterSupervisorRepository $masters)
    {
        foreach ($masters->names() as $master) {
            if (Str::startsWith($master, MasterSupervisor::basename())) {
                $this->purge($master);
            }
        }
    }

    /**
     * Purge any orphan processes.
     *
     * @param  string  $master
     * @return void
     */
    public function purge($master)
    {
        $this->recordOrphans($master);

        $expired = $this->processes->orphanedFor(
            $master, $this->supervisors->longestActiveTimeout()
        );

        collect($expired)->each(function ($processId) use ($master) {
            $this->comment("Killing Process: {$processId}");

            exec("kill {$processId}");

            $this->processes->forgetOrphans($master, [$processId]);
        });
    }

    /**
     * Record the orphaned Horizon processes.
     *
     * @param  string  $master
     * @return void
     */
    protected function recordOrphans($master)
    {
        $this->processes->orphaned(
            $master, $orphans = $this->inspector->orphaned()
        );

        foreach ($orphans as $processId) {
            $this->info("Observed Orphan: {$processId}");

            if (! posix_kill($processId, SIGTERM)) {
                $this->error("Failed to kill process for Orphan: {$processId} (".posix_strerror(posix_get_last_error()).')');
            }
        }
    }
}
