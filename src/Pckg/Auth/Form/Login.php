<?php

namespace Pckg\Auth\Form;

use Pckg\Auth\Form\Validator\ExistingUser;
use Pckg\Auth\Form\Validator\UserEmail;
use Pckg\Htmlbuilder\Element\Form\Bootstrap;
use Pckg\Htmlbuilder\Element\Form\ResolvesOnRequest;

/**
 * Class Login
 *
 * @package Pckg\Auth\Form
 */
class Login extends Bootstrap implements ResolvesOnRequest
{

    /**
     * @return $this
     */
    public function initFields()
    {
        $fieldset = $this->addFieldset();

        $fieldset->addText('email')
                 ->setLabel('Email:')
                 ->addValidator(new ExistingUser())
                 ->required();

        $fieldset->addPassword('password')
                 ->setLabel('Password:')
                 ->required();

        $this->addSubmit();

        return $this;
    }
}