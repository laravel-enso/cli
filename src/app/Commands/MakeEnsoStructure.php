<?php

namespace LaravelEnso\StructureManager\app\Commands;

use Illuminate\Console\Command;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\StructureManager\app\Classes\Helpers\Symbol;
use LaravelEnso\StructureManager\app\Classes\Helpers\Writer;
use LaravelEnso\StructureManager\app\Commands\Contextuals\Menu;

class MakeEnsoStructure extends Command
{
    const Choices = [
        'Model',
        'Permission Group',
        'Permissions',
        'Menu',
        'Files',
        'Generate'
    ];

    protected $signature = 'enso:make:structure';

    protected $description = 'Create a new Laravel Enso Structure';

    private $choices;

    private $configured;

    public function __construct()
    {
        parent::__construct();

        $this->configured = collect();

        $this->setChoices();
    }

    public function handle()
    {
        $this->info('Create a new Laravel Enso Structure');

        $this->line('');

        $this->index();
    }

    private function index()
    {
        $this->status();

        $choice = $this->choice('Choose element to configure', self::Choices);

        if ($this->choices()->contains($choice)) {
            $this->fill($choice);
        }

        if ($choice === $this->action()) {
            $this->attemptWrite();

            return;
        }

        $this->index();
    }

    private function fill($choice)
    {
        if ($this->missesRequired($choice)) {
            return;
        }

        $this->info(title_case($choice).' configuration:');

        $this->showConfiguration($choice);

        if ($this->confirm('Configure '.title_case($choice))) {
            $this->setConfiguration($choice);
        }
    }

    private function showConfiguration($choice)
    {
        $config = $this->choices->get(camel_case($choice));

        collect($config->keys())
            ->each(function ($key) use ($config) {
                $this->line(
                    $key.' => '.(
                        is_bool($config->get($key))
                            ? Symbol::bool($config->get($key))
                            : $config->get($key)
                    )
                );
            });
    }

    private function setConfiguration($choice)
    {
        $config = $this->choices->get(camel_case($choice));

        collect($config->keys())
            ->each(function ($key) use ($config) {
                $input = $this->input($config, $key);
                $config->set($key, $input);
            });

        if (!$this->configured->contains($choice)) {
            $this->configured->push($choice);
        }
    }

    private function input($config, $key)
    {
        $type = gettype($config->get($key));

        $value = is_bool($config->get($key))
            ? $this->confirm($key)
            : $this->anticipate($key, [$config->get($key)]);

        if ($this->isValid($type, $value)) {
            return $type === 'integer'
                ? intval($value)
                : $value;
        }

        $this->error($key.' must be of type '.$type);
        sleep(1);

        return $this->input($config, $key);
    }

    private function isValid($type, $value)
    {
        return $type === 'NULL'
            || ($type === 'integer' && (string) intval($value) === $value)
            || (gettype($value) === $type);
    }

    private function status()
    {
        $this->line('Current configuration status:');

        $status = $this->choices()->each(function ($choice) {
            $this->line($choice.' '.(Symbol::bool($this->configured->contains($choice))));
        });
    }

    private function missesRequired($choice)
    {
        $diff = $this->requires($choice)
            ->diff($this->configured);

        if ($diff->isNotEmpty()) {
            $this->info('You must configure first: '.'<fg=yellow>'.$diff->implode(', ').'</>');
            $this->line('');
            sleep(1);
        }

        return $diff->isNotEmpty();
    }

    private function setChoices()
    {
        $this->choices = new Obj;

        $this->choices()->each(function ($choice) {
            $this->choices->set(
                camel_case($choice),
                $this->attributes($choice)
            );
        });
    }

    private function attributes($choice)
    {
        return new Obj($this->config($choice, 'attributes'));
    }

    private function requires($choice)
    {
        return collect($this->config($choice, 'requires'));
    }

    private function config($choice, $param)
    {
        return config('enso.structures.'.camel_case($choice).'.'.$param);
    }

    private function action()
    {
        return collect(self::Choices)->pop();
    }

    private function choices()
    {
        return collect(self::Choices)->slice(0, -1);
    }

    private function attemptWrite()
    {
        if ($this->configured->isEmpty()) {
            $this->error('There is nothing configured yet!');
            $this->line('');
            sleep(1);
            $this->index();

            return;
        }

        $this->write();
    }

    private function write()
    {
        collect($this->choices->keys())
            ->each(function ($key) {
                if (!$this->configured->first(function ($attribute) use ($key) {
                    return camel_case($attribute) === $key;
                })) {
                    $this->choices->forget($key);
                }
            });

        (new Writer($this->choices))->run();

        $this->info('The new structure is created, start playing');
    }
}
