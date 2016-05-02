<?php

namespace Pckg\Auth\Form;

use Pckg\Htmlbuilder\Element\Form\Bootstrap;

/**
 * Class ForgotPassword
 * @package Pckg\Auth\Form
 */
class ForgotPassword extends Bootstrap
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

        $this->addSubmit();

        return $this;
    }

}