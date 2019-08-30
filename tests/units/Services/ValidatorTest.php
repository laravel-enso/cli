<?php

namespace LaravelEnso\Cli\tests\units\Services;

use Faker\Factory;
use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use LaravelEnso\Menus\app\Models\Menu;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Cli\app\Services\Validator;

class ValidatorTest extends TestCase
{
    private $faker;
    private $choices;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        $this->choices = new Obj([
            'permissions' => ['create' => 'create'],
        ]);

        $this->createMenuTable();
    }

    /** @test */
    public function cannot_validate_two_slashed_namespace_model()
    {
        $this->choices->put('model', new Obj(['name' => 'namespace//testModel']));

        $validator = (new Validator($this->choices, collect(['model'])))->run();

        $this->assertTrue($validator->fails());
        $this->assertEquals('Namespaced models must only use one slash for each segment',
            $validator->errors()->get('Model')->first());
    }

    /** @test */
    public function cannot_validate_back_slashed_namespace_model()
    {
        $this->choices->put('model', new Obj(['name' => 'namespace\\testModel']));

        $validator = (new Validator($this->choices, collect(['model'])))->run();

        $this->assertTrue($validator->fails());
        $this->assertEquals('Namespaced models must only use slashes ("/")',
            $validator->errors()->get('Model')->first());
    }

    /** @test */
    public function can_validate_model()
    {
        $this->choices->put('model', new Obj(['name' => 'namespace/testModel']));

        $validator = (new Validator($this->choices, collect(['model'])))->run();

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function cannot_validate_parent_menu_route()
    {
        $this->choices->put('menu', new Obj([
            'route' => 'route',
            'has_children' => true,
        ]));

        $validator = (new Validator($this->choices, collect(['menu'])))->run();

        $this->assertTrue($validator->fails());
        $this->assertEquals('A parent menu must have the route attribute empty',
            $validator->errors()->get('Menu')->first());
    }

    /** @test */
    public function cannot_validate_menu_with_wrong_permission_group()
    {
        $this->choices->put('menu', new Obj(['route' => 'wrong_group.route']));

        $this->choices->put('permissionGroup', new Obj(['name' => 'group']));

        $validator = (new Validator($this->choices, collect(['menu'])))->run();

        $this->assertTrue($validator->fails());
        $this->assertEquals('The menu\'s route does not match the configured permission group',
            $validator->errors()->get('Menu')->first());
    }

    /** @test */
    public function cannot_validate_menu_with_wrong_permission()
    {
        $this->choices->put('menu', new Obj(['route' => 'route.wrong_perm']));

        $validator = (new Validator($this->choices, collect(['menu'])))->run();

        $this->assertTrue($validator->fails());
        $this->assertEquals('The menu\'s route does not match the configured permissions',
            $validator->errors()->get('Menu')->first());
    }

    /** @test */
    public function cannot_validate_regular_menu_without_route()
    {
        $this->choices->put('menu', new Obj([]));

        $validator = (new Validator($this->choices, collect(['menu'])))->run();

        $this->assertTrue($validator->fails());
        $this->assertEquals('A regular menu must have the route attribute filled',
            $validator->errors()->get('Menu')->first());
    }

    /** @test */
    public function cannot_validate_menu_when_no_parent_exist()
    {
        $this->choices->put('menu', new Obj([
            'route' => 'route.create',
            'parentMenu' => 'not_menu',
        ]));

        $validator = (new Validator($this->choices, collect(['menu'])))->run();

        $this->assertTrue($validator->fails());
        $this->assertEquals('The parent menu not_menu does not exist in the system',
            $validator->errors()->get('Menu')->first());
    }

    /** @test */
    public function cannot_validate_menu_when_more_than_one_parent_exist()
    {
        $this->choices->put('menu', new Obj([
            'route' => 'route.create',
            'parentMenu' => 'parent',
        ]));

        $this->createParentMenu('parent')->createParentMenu('parent');

        $validator = (new Validator($this->choices, collect(['menu'])))->run();

        $this->assertTrue($validator->fails());
        $this->assertEquals('The parent menu parent is ambiguous. Please use dotted notation to specify its parent too.',
            $validator->errors()->get('Menu')->first());
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
}
