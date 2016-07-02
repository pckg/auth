<?php

namespace Pckg\Auth\Form;

use Pckg\Htmlbuilder\Element\Form\Bootstrap;
use Pckg\Auth\Form\Validator\UserEmail;
use Pckg\Htmlbuilder\Element\Form\ResolvesOnRequest;

/**
 * Class Login
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
            ->addValidator(new UserEmail())
            ->required();

        $fieldset->addPassword('password')
            ->setLabel('Password:')
            ->required();

        $fieldset->addCheckbox('autologin')
            ->setLabel('Autologin?');

        $this->addSubmit();

        return $this;
    }
}