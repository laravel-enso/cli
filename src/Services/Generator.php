<?php

namespace LaravelEnso\Cli\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelEnso\Cli\Services\Writers\RouteGenerator;

class Generator
{
    public function __construct(private Choices $choices)
    {
    }

    public function handle()
    {
        if ($this->isNotConfigured() || $this->failsValidation()) {
            return false;
        }

        $this->filterUnconfigured()
            ->write()
            ->output();

        $this->choices->clearCache();

        return true;
    }

    private function isNotConfigured()
    {
        if (!$this->choices->isConfigured()) {
            $this->console()->error('There is nothing configured yet!');
            $this->console()->newLine();

            sleep(1);

            return true;
        }

        return false;
    }

    private function failsValidation()
    {
        if (!$this->choices->needsValidation()) {
            return false;
        }

        $validator = (new Validator($this->choices))->run();
        $this->choices->errors($validator->errors());

        if ($validator->fails()) {
            $this->outputErrors($validator);

            return true;
        }

        return false;
    }

    private function outputErrors(Validator $validator)
    {
        $this->console()->warn('Your configuration has errors:');
        $this->console()->newLine();

        $validator->errors()
            ->each(fn ($errors, $type) => $this->outputTypeErrors($errors, $type));

        $this->console()->newLine();

        sleep(1);
    }

    private function outputTypeErrors(Collection $errors, string $type)
    {
        $symbol = Symbol::exclamation();

        $this->console()->info("{$type} {$symbol}");

        $errors->each(fn ($error) => $this->console()->warn("    {$error}"));
    }

    private function filterUnconfigured()
    {
        $this->choices->keys()
            ->reject(
                fn ($key) => $this->choices->configured()
                    ->first(fn ($attribute) => Str::camel($attribute) === $key)
            )->each(fn ($key) => $this->choices->forget($key));

        if ($this->choices->filled('files')) {
            $this->choices->get('files')
                ->filter(fn ($chosen) => !$chosen)
                ->keys()
                ->each(fn ($file) => $this->choices->get('files')->forget($file));
        }

        return $this;
    }

    private function write()
    {
        (new Structure($this->choices))->handle();

        return $this;
    }

    private function output()
    {
        if ($this->choices->has('permissions')) {
            $routes = (new RouteGenerator($this->choices))->handle();

            if ($routes) {
                $this->outputRoutes($routes);
            }
        }

        if (
            $this->choices->filled('package')
            && $this->choices->get('package')->get('config')
        ) {
            $this->outputPackageInfo();
        }

        $this->console()->newLine();
    }

    private function outputRoutes($routes)
    {
        $this->console()->info('Please add this line to your routes/api.php');
        $this->console()->newLine();
        $this->console()->warn("require __DIR__.'/$routes';");
        $this->console()->newLine();
    }

    private function outputPackageInfo()
    {
        $message = 'Your package is created, you can start playing.';
        $message .= " Don't forget to run `git init` in the package root folder!";
        $this->console()->info($message);

        $message = 'Add your package namespace and path inside your `composer.json`';
        $message .= ' file under the `psr-4` key while developing.';
        $this->console()->warn($message);

        if (
            $this->choices->filled('package')
            && $this->choices->get('package')->get('providers')
        ) {
            $this->console()->warn(
                'Add the package`s service provider to the `config/app.php` list of providers.'
            );
        }
    }

    private function console()
    {
        return $this->choices->console();
    }
}
