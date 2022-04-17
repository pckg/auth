<?php

namespace Pckg\Auth\Form;

use Pckg\Auth\Form\Validator\ExistingUser;
use Pckg\Htmlbuilder\Element\Form\Bootstrap;
use Pckg\Htmlbuilder\Element\Form\ResolvesOnRequest;

/**
 * Class ForgotPassword
 *
 * @package Pckg\Auth\Form
 */
class ForgotPassword extends Bootstrap implements ResolvesOnRequest
{
    /**
     * @return $this
     */
    public function initFields()
    {
        $this->addEmail('email')->setLabel('Email')->addValidator(new ExistingUser())->required();

        $this->addSubmit();

        return $this;
    }
}
