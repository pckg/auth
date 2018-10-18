<?php namespace Pckg\Auth\Form\Validator;

use Pckg\Auth\Entity\Users;
use Pckg\Htmlbuilder\Validator\AbstractValidator;

class ExistingUser extends AbstractValidator
{

    protected $recursive = false;

    protected $msg = 'User with email doesn\'t exist';

    public function validate($value)
    {
        return (new Users())->where('email', $value)->one() ? true : false;
    }

}