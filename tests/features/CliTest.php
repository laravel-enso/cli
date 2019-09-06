<?php

namespace LaravelEnso\Cli\tests\features;

use Tests\TestCase;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use LaravelEnso\Cli\app\Enums\Options;
use LaravelEnso\Helpers\app\Classes\Obj;

class CliTest extends TestCase
{
    private $choice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->choice = Options::choices()->first();

        Config::set("enso.structures{Str::camel($this->choice)}.attributes", ['name' => null]);
    }

    protected function tearDown() :void
    {
        Cache::forget('cli_params');
        Cache::forget('cli_choices');
        Cache::forget('cli_configured');

        parent::tearDown();
    }

    /** @test */
    public function cannot_config_choice_without_requirements()
    {
        $dependent = $this->dependent();
        $requirements = $this->requirements($dependent);

        $this->artisan('enso:cli')
            ->expectsQuestion('Choose element to configure', $dependent)
            ->expectsOutput('You must configure first: '.$requirements)
            ->expectsQuestion('Choose element to configure', Options::Exit);
    }

    /** @test */
    public function can_reload_session_if_available()
    {
        Cache::put('cli_data', [
            'params' => new Obj(),
            'choices' => new Obj(),
            'configured' => collect(),
            'validates' => true,
        ]);

        $this->artisan('enso:cli')
            ->expectsQuestion('Do you want to restore the last session?', 'yes')
            ->expectsQuestion('Choose element to configure', Options::Exit);
    }

    /** @test */
    public function when_choice_was_configured_should_save_config()
    {
        $this->artisan('enso:cli')
            ->expectsQuestion('Choose element to configure', $this->choice)
            ->expectsQuestion('Configure '.$this->choice, true)
            ->expectsQuestion('name', 'test')
            ->expectsQuestion('Choose element to configure', Options::Exit);

        $this->assertEquals('test', Cache::get('cli_data')['choices'][Str::camel($this->choice)]['name']);
    }

    /** @test */
    public function should_remove_saved_session_after_generate()
    {
        $this->artisan('enso:cli')
            ->expectsQuestion('Choose element to configure', $this->choice)
            ->expectsQuestion('Configure '.$this->choice, true)
            ->expectsQuestion('name', 'test')
            ->expectsQuestion('Choose element to configure', Options::Generate);

        $this->assertFalse(Cache::has('cli_data'));
    }

    /** @test */
    public function should_validate()
    {
        $this->artisan('enso:cli')
            ->expectsQuestion('Choose element to configure', Options::Model)
            ->expectsQuestion('Configure '.Options::Model, true)
            ->expectsQuestion('name', 'test\\test')
            ->expectsQuestion('Choose element to configure', Options::Generate);

        $this->assertEquals('test', Arr::get(SpyValidator::$choices, 'independent.name'));
    }

    /** @test */
    public function when_generate_called_and_validate_is_not_enable_should_not_validate()
    {
        $this->artisan('enso:cli')
            ->expectsQuestion('Choose element to configure', TestMenus::Independent)
            ->expectsQuestion('Configure '.TestMenus::Independent, true)
            ->expectsQuestion('name', 'test')
            ->expectsQuestion('Choose element to configure', TestMenus::ToggleValidation)
            ->expectsQuestion('Choose element to configure', TestMenus::Generate);

        $this->assertNull(SpyValidator::$choices);
    }

    /** @test */
    public function cannot_generate_with_nothing_configured()
    {
        $this->artisan('enso:cli')
            ->expectsQuestion('Choose element to configure', Options::Generate)
            ->expectsOutput('There is nothing configured yet!')
            ->expectsQuestion('Choose element to configure', Options::Exit);
    }

    /** @test */
    public function when_generate_called_and_validator_failed_then_should_not_call_structure()
    {
        SpyValidator::$fails = true;

        $this->artisan('enso:cli')
            ->expectsQuestion('Choose element to configure', TestMenus::Independent)
            ->expectsQuestion('Configure '.TestMenus::Independent, true)
            ->expectsQuestion('name', 'test')
            ->expectsQuestion('Choose element to configure', TestMenus::Generate)
            ->expectsQuestion('Choose element to configure', TestMenus::Exit);

        $this->assertEquals(null, SpyStructure::$choices);
    }

    /** @test */
    public function when_generate_called_then_should_call_structure()
    {
        $this->artisan('enso:cli')
            ->expectsQuestion('Choose element to configure', TestMenus::Independent)
            ->expectsQuestion('Configure '.TestMenus::Independent, true)
            ->expectsQuestion('name', 'test')
            ->expectsQuestion('Choose element to configure', TestMenus::Generate);

        $this->assertEquals('test', Arr::get(SpyStructure::$choices, 'independent.name'));
    }

    private function dependent()
    {
        return collect(Options::choices())->first(function ($choice) {
            return ! empty(config('enso.structures.'.Str::camel($choice).'.requires'));
        });
    }

    private function requirements($choice)
    {
        return collect(
            config('enso.structures.'.Str::camel($choice).'.requires')
        )->implode(', ');
    }
}

class SpyValidator
{
    public static $choices;
    public static $configured;
    public static $fails;

    public function __construct(Obj $choices, $configured)
    {
        self::$choices = $choices;
        self::$configured = $configured;
    }

    public static function refresh()
    {
        self::$choices = self::$configured = null;
        self::$fails = false;
    }

    public function run()
    {
        return $this;
    }

    public function fails()
    {
        return self::$fails;
    }

    public function errors()
    {
        return collect();
    }
}

class SpyStructure
{
    public static $choices;
    public static $params;

    public function __construct(Obj $choices, Obj $params)
    {
        self::$choices = $choices;
        self::$params = $params;
    }

    public static function refresh()
    {
        self::$choices = self::$params = null;
    }

    public function run()
    {
        return $this;
    }

    public function handle()
    {
        return false;
    }
}
