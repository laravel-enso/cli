<?php

namespace LaravelEnso\Cli\Services\Writers;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use LaravelEnso\Cli\Services\Choices;
use LaravelEnso\Cli\Services\Writers\Helpers\Directory;
use LaravelEnso\Cli\Services\Writers\Helpers\Stub;
use LaravelEnso\Helpers\Services\Obj;

class Package
{
    private const BaseFiles = ['README.md', 'LICENSE', 'composer.json'];
    private const ServiceProviders = ['AppServiceProvider.php', 'AuthServiceProvider.php'];

    private Obj $package;
    private string $root;
    private string $namespace;

    public function __construct(Choices $choices)
    {
        $this->package = $choices->get('package');
        $this->root = $choices->params()->get('root');
        $this->namespace = $choices->params()->get('namespace');
    }

    public function handle()
    {
        $this->files()->each(fn ($file) => $this->file($file));
    }

    private function files()
    {
        Stub::folder('package');
        Directory::prepare($this->path());

        $files = new Collection(self::BaseFiles);

        if ($this->package->get('config')) {
            Directory::prepare($this->path('config'));
            $files->push('config');
        }

        return $this->package->get('providers')
            ? $files->concat(self::ServiceProviders)
            : $files;
    }

    private function file(string $file)
    {
        $filePath = $file === 'config'
            ? $this->path(['config', "{$this->package->get('name')}.php"])
            : $this->path($file);

        File::put($filePath, $this->content($file));
    }

    private function content(string $file)
    {
        $fromTo = $this->fromTo();
        [$from, $to] = [array_keys($fromTo), array_values($fromTo)];
        [$name] = explode('.', $file);
        $content = str_replace($from, $to, Stub::get($name));

        return $name === 'composer' ? $this->composer($content) : $content;
    }

    private function composer(string $content)
    {
        $array = json_decode($content, true);

        return json_encode($array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    private function fromTo()
    {
        $segments = (new Collection(explode('\\', $this->namespace)))->slice(0, 2);

        return [
            '${year}' => Carbon::now()->format('Y'),
            '${vendor}' => $this->package->get('vendor'),
            '${package}' => $this->package->get('name'),
            '${namespace}' => $segments->implode('\\'),
            '${Vendor}' => $segments->first(),
            '${Package}' => $segments->last(),
        ];
    }

    private function path($segments = null)
    {
        return (new Collection([$this->root, ...(array) $segments]))
            ->filter()->implode(DIRECTORY_SEPARATOR);
    }
}
