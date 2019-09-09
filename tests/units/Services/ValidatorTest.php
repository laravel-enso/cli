<?php

namespace LaravelEnso\Cli\tests\units\Services;

use Faker\Factory;
use Tests\TestCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use LaravelEnso\Menus\app\Models\Menu;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Cli\app\Services\Choices;
use LaravelEnso\Cli\app\Services\Validator;

class ValidatorTest extends TestCase
{
    private $faker;
    private $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        $this->createMenuTable();
    }

    /** @test */
    public function cannot_validate_two_slashed_namespace_model()
    {
        $choices = $this->modelChoices('namespace//testModel');

        $this->validator = (new Validator($choices))->run();

        $this->assertErrors('Model', 'Namespaced models must only use one slash for each segment');
    }

    /** @test */
    public function cannot_validate_back_slashed_namespace_model()
    {
        $choices = $this->modelChoices('namespace\\testModel');

        $this->validator = (new Validator($choices))->run();

        $this->assertErrors('Model', 'Namespaced models must only use slashes ("/")');
    }

    /** @test */
    public function can_validate_model()
    {
        $choices = $this->modelChoices('namespace/testModel');

        $this->validator = (new Validator($choices))->run();

        $this->assertFalse($this->validator->fails());
    }

    /** @test */
    public function cannot_validate_parent_menu_route()
    {
        $choices = $this->menuChoices('route', null, true);

        $this->validator = (new Validator($choices))->run();

        $this->assertErrors('Menu', 'A parent menu must have the route attribute empty');
    }

    /** @test */
    public function cannot_validate_menu_with_wrong_permission_group()
    {
        $choices = $this->menuChoices('wrong_group.route');
        $choices->put('permissionGroup', new Obj(['name' => 'group']));

        $this->validator = (new Validator($choices))->run();

        $this->assertErrors('Menu', 'The menu\'s route does not match the configured permission group');
    }

    /** @test */
    public function cannot_validate_menu_with_wrong_permission()
    {
        $choices = $this->menuChoices('route.wrong_perm');

        $this->validator = (new Validator($choices))->run();

        $this->assertErrors('Menu', 'The menu\'s route does not match the configured permissions');
    }

    /** @test */
    public function cannot_validate_regular_menu_without_route()
    {
        $choices = $this->menuChoices(null);

        $this->validator = (new Validator($choices))->run();

        $this->assertErrors('Menu', 'A regular menu must have the route attribute filled');
    }

    /** @test */
    public function cannot_validate_menu_when_no_parent_exist()
    {
        $choices = $this->menuChoices('route.create', 'not_menu');

        $this->validator = (new Validator($choices))->run();

        $this->assertErrors('Menu', 'The parent menu not_menu does not exist in the system');
    }

    /** @test */
    public function cannot_validate_menu_when_more_than_one_parent_exist()
    {
        $choices = $this->menuChoices('route.create', 'parent');
        $this->createParentMenu('parent')->createParentMenu('parent');

        $this->validator = (new Validator($choices))->run();

        $this->assertErrors('Menu', 'The parent menu parent is ambiguous. Please use dotted notation to specify its parent too.');
    }

    private function createMenuTable()
    {
        Schema::create('menus', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->string('has_children');
            $table->timestamps();
        });
    }

    private function createParentMenu($name)
    {
        Menu::create(['name' => $name, 'has_children' => true]);

        return $this;
    }

    private function modelChoices($name)
    {
        return (new Choices(new Command))
            ->setChoices(new Obj(['model' => ['name' => $name]]))
            ->setConfigured(collect(['Model']));
    }

    private function menuChoices($route, $parentMenu = null, $hasChildren = false)
    {
        return (new Choices(new Command))
            ->setChoices(new Obj([
                'menu' => $this->menu($route, $parentMenu, $hasChildren),
                'permissions' => $this->permission('create')]))
            ->setConfigured(collect(['Menu']));
    }

    private function assertErrors($config, $desc)
    {
        $this->assertTrue($this->validator->fails());
        $this->assertEquals($desc, $this->validator->errors()->get($config)->first());
    }

    private function menu($route, $parentMenu, $hasChildren)
    {
        return new Obj([
            'route' => $route,
            'parentMenu' => $parentMenu,
            'has_children' => $hasChildren,
        ]);
    }

    private function permission($permission)
    {
        return new Obj([$permission => $permission]);
    }
}
