<?php

namespace LaravelEnso\Cli\tests\features;

use Tests\TestCase;
use Illuminate\Support\Arr;
use LaravelEnso\Cli\app\Enums\Menus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Cli\app\Services\Structure;
use LaravelEnso\Cli\app\Services\Validator;

class CliTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->bind(Menus::class, TestMenus::class);
        $this->app->bind(Validator::class, SpyValidator::class);
        $this->app->bind(Structure::class, SpyStructure::class);

        Config::set('enso.structures.depended.requires', [TestMenus::Independent]);
        Config::set('enso.structures.independent.requires', []);
        Config::set('enso.structures.independent.attributes', ['name' => null]);
        Config::set('enso.structures.depended.attributes', ['name' => null]);

        SpyStructure::refresh();
        SpyValidator::refresh();
    }

    protected function tearDown() :void
    {
        Cache::forget('cli_params');
        Cache::forget('cli_choices');
        Cache::forget('cli_configured');

        parent::tearDown();
    }

    /** @test */
    public function cannot_config_depended_menu_before_its_dependencies()
    {
        $this->artisan('enso:cli')
            ->expectsQuestion('Choose element to configure', TestMenus::Depended)
            ->expectsOutput('You must configure first: '.TestMenus::Independent)
            ->expectsQuestion('Choose element to configure', TestMenus::Close);
    }

    /** @test */
    public function when_there_was_a_session_then_should_ask_to_reload_session()
    {
        Cache::put('cli_data', [new Obj(), new Obj(), collect(), true]);

        $this->artisan('enso:cli')
            ->expectsQuestion('Do you want to continue the last session?', 'yes')
            ->expectsQuestion('Choose element to configure', TestMenus::Close);
    }

    /** @test */
    public function when_session_was_closed_then_should_save_config()
    {
        $this->artisan('enso:cli')
            ->expectsQuestion('Choose element to configure', TestMenus::Independent)
            ->expectsQuestion('Configure '.TestMenus::Independent, true)
            ->expectsQuestion('name', 'test')
            ->expectsQuestion('Choose element to configure', TestMenus::Close);

        $this->assertEquals('test', Arr::get(Cache::get('cli_data', [])[1], 'independent.name'));
    }

    /** @test */
    public function when_generate_called_should_remove_saved_session()
    {
        $this->artisan('enso:cli')
            ->expectsQuestion('Choose element to configure', TestMenus::Independent)
            ->expectsQuestion('Configure '.TestMenus::Independent, true)
            ->expectsQuestion('name', 'test')
            ->expectsQuestion('Choose element to configure', TestMenus::Generate);

        $this->assertFalse(Cache::has('cli_choices') || Cache::has('cli_params')
            || Cache::has('cli_configured'));
    }

    /** @test */
    public function when_generate_called_and_validate_is_enable_should_validate()
    {
        $this->artisan('enso:cli')
            ->expectsQuestion('Choose element to configure', TestMenus::Independent)
            ->expectsQuestion('Configure '.TestMenus::Independent, true)
            ->expectsQuestion('name', 'test')
            ->expectsQuestion('Choose element to configure', TestMenus::Generate);

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
    public function when_generate_called_and_no_fields_configured_then_should_not_call_structure()
    {
        $this->artisan('enso:cli')
            ->expectsQuestion('Choose element to configure', TestMenus::Generate)
            ->expectsOutput('There is nothing configured yet!')
            ->expectsQuestion('Choose element to configure', TestMenus::Close);

        $this->assertEquals(null, SpyStructure::$choices);
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
            ->expectsQuestion('Choose element to configure', TestMenus::Close);

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
}

class TestMenus extends Menus
{
    const Independent = 'Independent';
    const Depended = 'Depended';

    public static function choices()
    {
        return collect([
            self::Independent, self::Depended, self::Files,
        ]);
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
