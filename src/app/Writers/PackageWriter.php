<?php

namespace LaravelEnso\Cli\app\Writers;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Cli\app\Services\Choices;

class PackageWriter
{
    protected $choices;
    private $root;

    public function __construct(Choices $choices)
    {
        $this->choices = $choices;
    }

    public function handle()
    {
        $this->root = $this->params()->get('root');

        if (! File::isDirectory($this->root)) {
            File::makeDirectory($this->root, 0755, true);
        }

        [$from, $to] = $this->fromTo();

        File::put(
            $this->filename('composer.json'),
            $this->composer($from, $to)
        );

        File::put(
            $this->filename('README.md'),
            str_replace($from, $to, $this->stub('README.stub'))
        );

        File::put(
            $this->filename('LICENSE'),
            str_replace($from, $to, $this->stub('LICENSE.stub'))
        );

        $this->createConfig($from, $to)
            ->createProviders($from, $to);
    }

    private function filename($filename)
    {
        return $this->root.$filename;
    }

    private function stub($file)
    {
        return File::get(
            __DIR__.DIRECTORY_SEPARATOR.'stubs'
            .DIRECTORY_SEPARATOR.'package'
            .DIRECTORY_SEPARATOR.$file
        );
    }

    private function fromTo()
    {
        $array = [
            '${year}' => Carbon::now()->format('Y'),
            '${vendor}' => $this->choices->get('package')->get('vendor'),
            '${package}' => $this->choices->get('package')->get('name'),
            '${namespace}' => $this->namespace(),
            '${Vendor}' => collect(explode('\\', $this->namespace()))->first(),
            '${Package}' => collect(explode('\\', $this->namespace()))->last(),
        ];

        return [
            array_keys($array),
            array_values($array),
        ];
    }

    private function createConfig($from, $to)
    {
        if ($this->choices->get('package')->get('config')) {
            if (! File::isDirectory($this->root.'config')) {
                File::makeDirectory($this->root.'config', 0755, true);
            }

            File::put(
                $this->filename('config'.DIRECTORY_SEPARATOR.$this->choices->get('package')->get('name').'.php'),
                str_replace($from, $to, $this->stub('config.stub'))
            );
        }

        return $this;
    }

    private function createProviders($from, $to)
    {
        if ($this->choices->get('package')->get('providers')) {
            File::put(
                $this->filename('AppServiceProvider.php'),
                str_replace($from, $to, $this->stub('AppServiceProvider.stub'))
            );

            File::put(
                $this->filename('AuthServiceProvider.php'),
                str_replace($from, $to, $this->stub('AuthServiceProvider.stub'))
            );
        }

        return $this;
    }

    private function composer($from, $to)
    {
        $replacedStr = str_replace($from, $to, $this->stub('composer.stub'));
        $array = json_decode($replacedStr, true);

        return json_encode($array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    private function namespace()
    {
        return collect(explode('\\', $this->params()->get('namespace')))
            ->slice(0, -2)
            ->implode('\\');
    }

    private function params()
    {

        return $this->choices->params();
    }
}
