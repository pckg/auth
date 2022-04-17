<?php

namespace Pckg\Auth\Form\Validator;

use Pckg\Auth\Entity\Users;
use Pckg\Htmlbuilder\Validator\AbstractValidator;

/**
 * Class ExistingUser
 *
 * @package Pckg\Auth\Form\Validator
 */
class ExistingUser extends AbstractValidator
{
    /**
     * @var bool
     */
    protected $recursive = false;

    /**
     * @var string
     */
    protected $msg = 'User with email doesn\'t exist';

    /**
     * @param  $value
     * @return bool
     */
    public function validate($value)
    {
        return (new Users())->nonDeleted()->where('email', $value)->one() ? true : false;
    }
}
