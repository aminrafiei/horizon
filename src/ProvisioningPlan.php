<?php

namespace Aminrafiei\Horizon;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Aminrafiei\Horizon\Contracts\HorizonCommandQueue;
use Aminrafiei\Horizon\MasterSupervisorCommands\AddSupervisor;

class ProvisioningPlan
{
    /**
     * The master supervisor's name.
     *
     * @var string
     */
    public $master;

    /**
     * The raw provisioning plan.
     *
     * @var array
     */
    public $plan;

    /**
     * The parsed provisioning plan.
     *
     * @var array
     */
    public $parsed;

    /**
     * Create a new provisioning plan instance.
     *
     * @param  string  $master
     * @param  array  $plan
     * @return void
     */
    public function __construct($master, array $plan)
    {
        $this->plan = $plan;
        $this->master = $master;

        $this->parsed = $this->toSupervisorOptions();
    }

    /**
     * Get the current provisioning plan.
     *
     * @param  string  $master
     * @return static
     */
    public static function get($master)
    {
        return new static($master, config('horizon.environments'));
    }

    /**
     * Get all of the defined environments for the provisioning plan.
     *
     * @return array
     */
    public function environments()
    {
        return array_keys($this->parsed);
    }

    /**
     * Determine if the provisioning plan has a given environment.
     *
     * @param  string  $environment
     * @return bool
     */
    public function hasEnvironment($environment)
    {
        return array_key_exists($environment, $this->parsed);
    }

    /**
     * Deploy a provisioning plan to the current machine.
     *
     * @param  string  $environment
     * @return void
     */
    public function deploy($environment)
    {
        $supervisors = collect($this->parsed)->first(function ($_, $name) use ($environment) {
            return Str::is($name, $environment);
        });

        if (empty($supervisors)) {
            return;
        }

        foreach ($supervisors as $supervisor => $options) {
            $this->add($options);
        }
    }

    /**
     * Add a supervisor with the given options.
     *
     * @param  \Aminrafiei\Horizon\SupervisorOptions  $options
     * @return void
     */
    protected function add(SupervisorOptions $options)
    {
        app(HorizonCommandQueue::class)->push(
            MasterSupervisor::commandQueueFor($this->master),
            AddSupervisor::class,
            $options->toArray()
        );
    }

    /**
     * Get the SupervisorOptions for a given environment and supervisor.
     *
     * @param  string  $environment
     * @param  string  $supervisor
     * @return mixed
     */
    public function optionsFor($environment, $supervisor)
    {
        if (isset($this->parsed[$environment]) && isset($this->parsed[$environment][$supervisor])) {
            return $this->parsed[$environment][$supervisor];
        }
    }

    /**
     * Convert the provisioning plan into an array of SupervisorOptions.
     *
     * @return array
     */
    public function toSupervisorOptions()
    {
        return collect($this->plan)->mapWithKeys(function ($plan, $environment) {
            return [$environment => collect($plan)->mapWithKeys(function ($options, $supervisor) {
                return [$supervisor => $this->convert($supervisor, $options)];
            })];
        })->all();
    }

    /**
     * Convert the given array of options into a SupervisorOptions instance.
     *
     * @param  string  $supervisor
     * @param  array  $options
     * @return \Aminrafiei\Horizon\SupervisorOptions
     */
    protected function convert($supervisor, $options)
    {
        $options = collect($options)->mapWithKeys(function ($value, $key) {
            $key = $key === 'tries' ? 'max_tries' : $key;
            $key = $key === 'processes' ? 'max_processes' : $key;
            $value = $key === 'queue' && is_array($value) ? implode(',', $value) : $value;

            return [Str::camel($key) => $value];
        })->all();

        return SupervisorOptions::fromArray(
            Arr::add($options, 'name', $this->master.":{$supervisor}")
        );
    }
}
