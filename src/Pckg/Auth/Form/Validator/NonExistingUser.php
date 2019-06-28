<?php namespace Pckg\Auth\Form\Validator;

class NonExistingUser extends ExistingUser
{

    protected $msg = 'User with email already exists, please login';

    public function validate($value)
    {
        return !parent::validate($value);
    }

}