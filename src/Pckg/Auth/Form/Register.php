<?php

namespace Pckg\Auth\Form;

use Pckg\Htmlbuilder\Element\Form\Bootstrap;

/**
 * Class Register
 * @package Pckg\Auth\Form
 */
class Register extends Bootstrap
{

    /**
     * @return $this
     */
    public function initFields()
    {
        $fieldset = $this->addFieldset();

        $fieldset->addEmail('email')
            ->setLabel('Email:')
            ->required();

        $fieldset->addPassword('password')
            ->setLabel('Password:')
            ->required();

        $fieldset->addPassword('password2')
            ->setLabel('Repeat password:')
            ->required();

        $this->addSubmit();

        return $this;
    }
}