<?php namespace Pckg\Auth\Form\Validator;

use Pckg\Htmlbuilder\Validator\AbstractValidator;

class ValidPassword extends AbstractValidator
{

    protected $recursive = false;

    protected $msg = 'Password should be at least 8 characters long';

    public function validate($value)
    {
        return strlen($value) >= 8;
    }

}