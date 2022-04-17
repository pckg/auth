<?php

namespace Pckg\Auth\Form\Validator;

use Pckg\Htmlbuilder\Validator\AbstractValidator;

/**
 * Class ValidPassword
 *
 * @package Pckg\Auth\Form\Validator
 */
class ValidPassword extends AbstractValidator
{
    /**
     * @var bool
     */
    protected $recursive = false;

    /**
     * @var string
     */
    protected $msg = 'Password should be at least 8 characters long';

    /**
     * @param  $value
     * @return bool
     */
    public function validate($value)
    {
        return strlen($value) >= 8;
    }
}
