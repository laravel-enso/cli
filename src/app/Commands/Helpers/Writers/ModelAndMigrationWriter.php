<?php
/**
 * Created with luv for spa2.
 * User: mihai
 * Date: 7/5/18
 * Time: 2:57 PM.
 */

namespace LaravelEnso\StructureManager\app\Classes\Helpers\Writers;

use Illuminate\Support\Facades\Artisan;
use LaravelEnso\Helpers\app\Classes\Obj;

class ModelAndMigrationWriter
{
    private $structure;

    public function __construct(Obj $structure)
    {
        $this->structure = $structure;
    }

    public function run()
    {
        $params = $this->paramsArray();

        Artisan::call('make:model', $params);
    }

    private function paramsArray()
    {
        return [
            'name'        => $this->structure->get('model')->get('name'),
            '--force'     => true,
            '--migration' => $this->requiresMigration(),
        ];
    }

    private function requiresMigration()
    {
        return $this->structure->get('files')->get('migration');
    }
}
