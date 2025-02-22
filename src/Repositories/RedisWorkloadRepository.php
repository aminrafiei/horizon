<?php

namespace Aminrafiei\Horizon\Repositories;

use Illuminate\Support\Str;
use Aminrafiei\Horizon\WaitTimeCalculator;
use Aminrafiei\Horizon\Contracts\WorkloadRepository;
use Aminrafiei\Horizon\Contracts\SupervisorRepository;
use Illuminate\Contracts\Queue\Factory as QueueFactory;
use Aminrafiei\Horizon\Contracts\MasterSupervisorRepository;

class RedisWorkloadRepository implements WorkloadRepository
{
    /**
     * The queue factory implementation.
     *
     * @var \Illuminate\Contracts\Queue\Factory
     */
    public $queue;

    /**
     * The wait time calculator instance.
     *
     * @var \Aminrafiei\Horizon\WaitTimeCalculator
     */
    public $waitTime;

    /**
     * The master supervisor repository implementation.
     *
     * @var \Aminrafiei\Horizon\Contracts\MasterSupervisorRepository
     */
    private $masters;

    /**
     * The supervisor repository implementation.
     *
     * @var \Aminrafiei\Horizon\Contracts\SupervisorRepository
     */
    private $supervisors;

    /**
     * Create a new repository instance.
     *
     * @param  \Illuminate\Contracts\Queue\Factory  $queue
     * @param  \Aminrafiei\Horizon\WaitTimeCalculator  $waitTime
     * @param  \Aminrafiei\Horizon\Contracts\MasterSupervisorRepository  $masters
     * @param  \Aminrafiei\Horizon\Contracts\SupervisorRepository  $supervisors
     * @return void
     */
    public function __construct(QueueFactory $queue, WaitTimeCalculator $waitTime,
                                MasterSupervisorRepository $masters, SupervisorRepository $supervisors)
    {
        $this->queue = $queue;
        $this->masters = $masters;
        $this->waitTime = $waitTime;
        $this->supervisors = $supervisors;
    }

    /**
     * Get the current workload of each queue.
     *
     * @return array
     */
    public function get()
    {
        $processes = $this->processes();

        return collect($this->waitTime->calculate())
            ->map(function ($waitTime, $queue) use ($processes) {
                [$connection, $queueName] = explode(':', $queue, 2);

                $length = ! Str::contains($queue, ',')
                    ? $this->queue->connection($connection)->readyNow($queueName)
                    : collect(explode(',', $queueName))->sum(function ($queueName) use ($connection) {
                        return $this->queue->connection($connection)->readyNow($queueName);
                    });

                return [
                    'name' => $queueName,
                    'length' => $length,
                    'wait' => $waitTime,
                    'processes' => $processes[$queue] ?? 0,
                ];
            })->values()->toArray();
    }

    /**
     * Get the number of processes of each queue.
     *
     * @return array
     */
    private function processes()
    {
        return collect($this->supervisors->all())->pluck('processes')->reduce(function ($final, $queues) {
            foreach ($queues as $queue => $processes) {
                $final[$queue] = isset($final[$queue]) ? $final[$queue] + $processes : $processes;
            }

            return $final;
        }, []);
    }
}
