<?php

namespace Pckg\Auth\Form\Validator;

use Pckg\Concept\AbstractObject;
use Pckg\Htmlbuilder\Validator\AbstractValidator;

class UserEmail extends AbstractValidator
{

    protected $msg = 'User with email doesn\'t exist';

    public function overloadIsValid(callable $next, AbstractObject $context)
    {
        //$email = $context->getElement()->getValue();
        //var_dump($email);

        //die("validating user email");

        return $next();
    }

}