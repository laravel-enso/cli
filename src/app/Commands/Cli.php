<?php

namespace LaravelEnso\Cli\app\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use LaravelEnso\Helpers\app\Classes\Obj;
// use LaravelEnso\Cli\app\Helpers\TestConfig;
use LaravelEnso\Cli\app\Services\Structure;
use LaravelEnso\Cli\app\Services\Validator;
use LaravelEnso\Cli\app\Writers\RouteGenerator;
use LaravelEnso\Cli\app\Commands\Helpers\Symbol;

class Cli extends Command
{
    const Menu = [
        'Model', 'Permission Group', 'Permissions', 'Menu',
        'Files', 'Package', 'Generate', 'Toggle Validation',
    ];

    protected $signature = 'enso:cli';

    protected $description = 'Create a new Laravel Enso Structure';

    private $choices;
    private $params;
    private $configured;
    private $validates;
    private $validator;

    public function __construct()
    {
        parent::__construct();

        $this->configured = collect();

        $this->setChoices()
            ->setParams();

        $this->validates = true;
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

        $choice = $this->choice('Choose element to configure', self::Menu);

        if ($this->choices()->contains($choice)) {
            $this->fill($choice);
        }

        if ($choice === $this->action()) {
            $this->attemptWrite();

            return;
        }

        if ($choice === $this->validation()) {
            $this->validates = ! $this->validates;
            $this->error('Validation '.($this->validates ? 'enabled' : 'disabled'));
            $this->line('');
            sleep(1);
        }

        $this->index();
    }

    private function fill($choice)
    {
        if ($this->missesRequired($choice)) {
            return;
        }

        $this->info(title_case($choice).' configuration:');

        $this->displayConfiguration($choice);

        if ($this->confirm('Configure '.title_case($choice))) {
            $this->updateConfiguration($choice);
        }
    }

    private function displayConfiguration($choice)
    {
        $config = $this->choices->get(Str::camel($choice));

        $config->keys()->each(function ($key) use ($config) {
            $this->line(
                $key.' => '.(
                    is_bool($config->get($key))
                        ? Symbol::bool($config->get($key))
                        : $config->get($key)
                )
            );
        });
    }

    private function updateConfiguration($choice)
    {
        $config = $this->choices->get(Str::camel($choice));

        $config->keys()->each(function ($key) use ($config, $choice) {
            $input = $this->input($config, $key);
            $config->set($key, $input);
        });

        if (! $this->configured->contains($choice)) {
            $this->configured->push($choice);
        }
    }

    private function input($config, $key)
    {
        $type = gettype($config->get($key));

        $value = is_bool($config->get($key))
            ? $this->confirm($key)
            : $this->anticipate($key, [$config->get($key) ?? '']);

        if ($this->isValid($type, $value)) {
            return $type === 'integer'
                ? (int) $value
                : $value;
        }

        $this->error($key.' must be of type '.$type);
        sleep(1);

        return $this->input($config, $key);
    }

    private function isValid($type, $value)
    {
        return $type === 'NULL'
            || ($type === 'integer' && (string) $value === $value)
            || (gettype($value) === $type);
    }

    private function hasError($choice)
    {
        return $this->validator
            && $this->validator->errors()
                ->keys()
                ->contains($choice);
    }

    private function status()
    {
        $this->info('Current configuration status:');

        $this->choices()->each(function ($choice) {
            $this->line($choice.' '.(
                $this->hasError($choice)
                    ? Symbol::exclamation()
                    : Symbol::bool($this->configured->contains($choice))
                ));
            });

        if ($this->configured->isNotEmpty()) {
            $this->line('');
            $this->info('Will generate:');
            $this->line('structure migration');
            $this->choices->get('files')
                ->each(function ($chosen, $file) {
                    if ($chosen) {
                        $this->line($file);
                    }
                });
        }
    }

    private function attemptWrite()
    {
        // $this->choices = TestConfig::loadStructure();
        // $this->configured = $this->choices->keys();

        if ($this->validates && $this->failsValidation()) {
            $this->index();

            return;
        }

        $this->filter()
            ->write()
            ->output();
    }

    private function failsValidation()
    {
        if ($this->configured->isEmpty()) {
            $this->error('There is nothing configured yet!');
            $this->line('');
            sleep(1);

            return true;
        }

        $this->validator = (new Validator(
            $this->choices, $this->configured
        ))->run();

        if ($this->validator->fails()) {
            $this->warning('Your configuration has errors:');
            $this->line('');

            $this->validator->errors()
                ->each(function ($errors, $type) {
                    $this->info($type.' '.Symbol::exclamation());
                    $errors->each(function ($error) {
                        $this->warning('    '.$error);
                    });
                });

            sleep(1);
            $this->line('');

            return true;
        }

        return false;
    }

    private function filter()
    {
        $this->choices->keys()->each(function ($key) {
            if ($this->configured->first(function ($attribute) use ($key) {
                return Str::camel($attribute) === $key;
            }) === null) {
                $this->choices->forget($key);
            }
        });

        if ($this->choices->has('files')) {
            $this->choices->get('files')->each(function ($chosen, $type) {
                if (! $chosen) {
                    $this->choices->get('files')->forget($type);
                }
            });
        }

        return $this;
    }

    private function write()
    {
        (new Structure($this->choices, $this->params))->handle();

        return $this;
    }

    private function output()
    {
        if ($this->choices->has('permissions')) {
            $routes = (new RouteGenerator($this->choices, $this->params))->run();

            if ($routes) {
                $this->info('Copy and paste the following code into your api.php routes file:');
                $this->line('');
                $this->warning($routes);
                $this->line('');
            }
        }

        $this->info('The new structure is created, you can start playing');
        $this->line('');
    }

    private function warning($output)
    {
        return $this->line('<fg=yellow>'.$output.'</>');
    }

    private function missesRequired($choice)
    {
        $diff = $this->requires($choice)->diff($this->configured);

        if ($diff->isNotEmpty()) {
            $this->warning('You must configure first: '.$diff->implode(', '));
            $this->line('');
            sleep(1);
        }

        return $diff->isNotEmpty();
    }

    private function attributes($choice)
    {
        return new Obj($this->config($choice, 'attributes'));
    }

    private function requires($choice)
    {
        return new Obj($this->config($choice, 'requires'));
    }

    private function config($choice, $param)
    {
        return config('enso.structures.'.Str::camel($choice).'.'.$param);
    }

    private function action()
    {
        return collect(self::Menu)->slice(-2, 1)->first();
    }

    private function validation()
    {
        return collect(self::Menu)->pop();
    }

    private function choices()
    {
        return collect(self::Menu)->slice(0, -2);
    }

    private function setChoices()
    {
        $this->choices = new Obj();

        $this->choices()->each(function ($choice) {
            $this->choices->set(
                Str::camel($choice),
                $this->attributes($choice)
            );
        });

        return $this;
    }

    private function setParams()
    {
        $this->params = $this->attributes('params');

        return $this;
    }
}
