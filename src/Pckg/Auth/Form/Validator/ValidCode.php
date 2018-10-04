<?php namespace Pckg\Auth\Form\Validator;

use Pckg\Htmlbuilder\Validator\AbstractValidator;

class ValidCode extends AbstractValidator
{

    protected $recursive = false;

    protected $msg = 'Code is not valid or is expired';

    public function validate($value)
    {
        return true;
    }

}