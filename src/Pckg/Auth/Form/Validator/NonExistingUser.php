<?php

namespace Pckg\Auth\Form\Validator;

/**
 * Class NonExistingUser
 *
 * @package Pckg\Auth\Form\Validator
 */
class NonExistingUser extends ExistingUser
{

    /**
     * @var string
     */
    protected $msg = 'User with email already exists, please login';

    /**
     * @param  $value
     * @return bool
     */
    public function validate($value)
    {
        return !parent::validate($value);
    }
}
