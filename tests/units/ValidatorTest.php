<?php

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use LaravelEnso\Cli\App\Services\Choices;
use LaravelEnso\Cli\App\Services\Validator;
use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Menus\App\Models\Menu;
use Tests\TestCase;

class ValidatorTest extends TestCase
{
    private $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createMenuTable();
    }

    /** @test */
    public function cannot_validate_model_with_two_slashed()
    {
        $choices = $this->modelChoices('namespace//testModel');

        $this->validator = (new Validator($choices))->run();

        $this->assertErrors('Model', 'Namespaced models must only use one slash for each segment');
    }

    /** @test */
    public function cannot_validate_model_with_back_slashed()
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
    public function cannot_validate_parent_menu_with_route()
    {
        $choices = $this->menuChoices('group', 'route', null, true);

        $this->validator = (new Validator($choices))->run();

        $this->assertErrors('Menu', 'A parent menu must have the route attribute empty');
    }

    /** @test */
    public function cannot_validate_menu_with_wrong_permission()
    {
        $choices = $this->menuChoices('group', 'wrong_route');
        $choices->set('permissions', new Obj(['route' => 'route']));

        $this->validator = (new Validator($choices))->run();

        $this->assertErrors('Menu', "The menu's route does not match the configured permissions");
    }

    /** @test */
    public function cannot_validate_regular_menu_without_route()
    {
        $choices = $this->menuChoices(null, null);

        $this->validator = (new Validator($choices))->run();

        $this->assertErrors('Menu', 'A regular menu must have the route attribute filled');
    }

    /** @test */
    public function can_validate_parent_menu()
    {
        $great = Menu::create(['name' => 'great', 'has_children' => true]);
        $grand = Menu::create(['name' => 'grand', 'has_children' => true, 'parent_id' => $great->id]);
        Menu::create(['name' => 'parent', 'has_children' => true, 'parent_id' => $grand->id]);


        $choices = $this->menuChoices('group', 'create', 'great.grand.parent');

        $this->validator = (new Validator($choices))->run();

        $this->assertFalse($this->validator->fails());
    }

    /** @test */
    public function cannot_validate_with_wrong_parent()
    {
        $choices = $this->menuChoices('group', 'create', 'not_menu');

        $this->validator = (new Validator($choices))->run();

        $this->assertErrors('Menu', 'The parent menu not_menu does not exist in the system');
    }

    /** @test */
    public function cannot_validate_menu_with_multiple_parent()
    {
        Menu::insert([
            ['name' => 'parent', 'has_children' => true],
            ['name' => 'parent', 'has_children' => true]
        ]);

        $choices = $this->menuChoices('group', 'create', 'parent');

        $this->validator = (new Validator($choices))->run();

        $this->assertErrors('Menu', 'The parent menu parent is ambiguous. Please use dotted notation to specify its parent too.');
    }

    private function createMenuTable()
    {
        Schema::create('menus', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->string('has_children');
            $table->integer('parent_id')->nullable();
            $table->timestamps();
        });
    }

    private function modelChoices($name)
    {
        return (new Choices(new Command()))
            ->setChoices(new Obj(['model' => ['name' => $name]]))
            ->setConfigured(['Model']);
    }

    private function menuChoices($group, $permission, $parentMenu = null, $hasChildren = false)
    {
        return (new Choices(new Command()))
            ->setChoices(new Obj([
                'menu' => $this->menu($permission, $parentMenu, $hasChildren),
                'permissions' => new Obj([$permission => $permission]),
                'permissionGroup' => new Obj(['name' => $group]),
            ]))->setConfigured(['Menu']);
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
}
