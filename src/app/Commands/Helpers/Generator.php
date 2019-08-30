<?php

namespace LaravelEnso\Cli\app\Commands\Helpers;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use LaravelEnso\Cli\app\Services\Structure;
use LaravelEnso\Cli\app\Services\Validator;
use LaravelEnso\Cli\app\Writers\RouteGenerator;

class Generator
{
    private $console;
    private $cliData;

    public function __construct(Command $console, CliData $cliData)
    {
        $this->console = $console;
        $this->cliData = $cliData;
    }

    public function generate()
    {
        if ($this->cliData->validates() && $this->failsValidation()) {
            return false;
        }

        $this->filter()
            ->write()
            ->output();

        $this->cliData->clearSave();

        return true;
    }

    private function failsValidation()
    {
        if (! $this->cliData->hasConfigured()) {
            $this->console->error('There is nothing configured yet!');
            $this->console->line('');
            sleep(1);

            return true;
        }

        $this->cliData->setValidator(App::makeWith(Validator::class, [
            'choices' => $this->cliData->choices(),
            'configured' => $this->cliData->configured(),
        ])->run());

        if ($this->cliData->validator()->fails()) {
            $this->console->warn('Your configuration has errors:');
            $this->console->line('');

            $this->cliData->validator()->errors()
                ->each(function ($errors, $type) {
                    $this->console->info($type.' '.Symbol::exclamation());
                    $errors->each(function ($error) {
                        $this->console->warn('    '.$error);
                    });
                });

            sleep(1);
            $this->console->line('');

            return true;
        }

        return false;
    }

    private function filter()
    {
        $this->cliData->choices()->keys()->each(function ($key) {
            if ($this->cliData->configured()->first(function ($attribute) use ($key) {
                return Str::camel($attribute) === $key;
            }) === null) {
                $this->cliData->choices()->forget($key);
            }
        });

        if ($this->cliData->hasFiles()) {
            $this->cliData->files()->each(function ($chosen, $type) {
                if (! $chosen) {
                    $this->cliData->files()->forget($type);
                }
            });
        }

        return $this;
    }

    private function write()
    {
        App::makeWith(Structure::class, [
            'choices' => $this->cliData->choices(),
            'params' => $this->cliData->params(),
        ])->handle();

        return $this;
    }

    private function output()
    {
        if ($this->cliData->choices()->has('permissions')) {
            $routes = (new RouteGenerator($this->cliData->choices(), $this->cliData->params()))->run();

            if ($routes) {
                $this->console->info('Copy and paste the following code into your api.php routes file:');
                $this->console->line('');
                $this->console->warn($routes);
                $this->console->line('');
            }
        }

        if ((bool) optional($this->cliData->choices()->get('package'))->get('config')) {
            $this->console->info("Your package is created, you can start playing. Don't forget to run `git init` in the package root folder!");
            $this->console->warn('Add your package namespace and path inside your `composer.json` file under the `psr-4` key while developing.');
        }

        if ((bool) optional($this->cliData->choices()->get('package'))->get('providers')) {
            $this->console->warn('Remember to add the package`s service provider to the `config/app.php` list of providers.');
        }
        $this->console->line('');
    }
}
