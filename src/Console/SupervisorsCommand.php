<?php

namespace Aminrafiei\Horizon\Console;

use Illuminate\Console\Command;
use Aminrafiei\Horizon\Contracts\SupervisorRepository;

class SupervisorsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'horizon:supervisors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all of the supervisors';

    /**
     * Execute the console command.
     *
     * @param  \Aminrafiei\Horizon\Contracts\SupervisorRepository  $supervisors
     * @return void
     */
    public function handle(SupervisorRepository $supervisors)
    {
        $supervisors = $supervisors->all();

        if (empty($supervisors)) {
            return $this->info('No supervisors are running.');
        }

        $this->table([
            'Name', 'PID', 'Status', 'Workers', 'Balancing',
        ], collect($supervisors)->map(function ($supervisor) {
            return [
                $supervisor->name,
                $supervisor->pid,
                $supervisor->status,
                collect($supervisor->processes)->map(function ($count, $queue) {
                    return $queue.' ('.$count.')';
                })->implode(', '),
                $supervisor->options['balance'],
            ];
        })->all());
    }
}
