<?php

namespace Pckg\Auth\Form\Validator;

use Pckg\Concept\AbstractObject;
use Pckg\Htmlbuilder\Validator\AbstractValidator;

/**
 * Class UserEmail
 *
 * @package Pckg\Auth\Form\Validator
 */
class UserEmail extends AbstractValidator
{

    /**
     * @var string
     */
    protected $msg = 'User with email doesn\'t exist';

    /**
     * @param  callable       $next
     * @param  AbstractObject $context
     * @return bool|null
     */
    public function overloadIsValid(callable $next, AbstractObject $context)
    {
        //$email = $context->getElement()->getValue();
        //var_dump($email);

        //die("validating user email");

        return $next();
    }

}