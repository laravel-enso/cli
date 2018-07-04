<?php
/**
 * Created with luv for spa2.
 * User: mihai
 * Date: 7/4/18
 * Time: 11:58 AM.
 */

namespace LaravelEnso\StructureManager\app\Classes\Helpers\Validators;

//better name is needed, it's not a validator per se
class ModelValidator
{
    private $structure;

    public function __construct($structure)
    {
        $this->structure = $structure;
    }

    public function run()
    {
        $this->enforceCapitalization();
    }

    private function enforceCapitalization()
    {
        //better fixed with setter if model is object
        $this->structure->model->name =
            ucfirst($this->structure->model->name);
    }
}
