<?php

namespace Pckg\Auth\Form;

use Pckg\Htmlbuilder\Element\Form\Bootstrap;
use Pckg\Auth\Form\Validator\UserEmail;

/**
 * Class Login
 * @package Pckg\Auth\Form
 */
class Login extends Bootstrap
{

    /**
     * @return $this
     */
    public function initFields()
    {
        $fieldset = $this->addFieldset();

        $fieldset->addEmail('email')
            ->setLabel('Email:')
            ->addValidator(new UserEmail())
            ->required();

        $fieldset->addPassword('password')
            ->setLabel('Password:')
            ->required();

        $this->addSubmit();

        return $this;
    }
}