<?php

namespace Pckg\Auth\Form;

use Pckg\Auth\Form\Validator\ExistingUser;
use Pckg\Auth\Form\Validator\ValidCode;
use Pckg\Htmlbuilder\Element\Form\Bootstrap;
use Pckg\Htmlbuilder\Element\Form\ResolvesOnRequest;

/**
 * Class PasswordCode
 *
 * @package Pckg\Auth\Form
 */
class PasswordCode extends Bootstrap implements ResolvesOnRequest
{

    /**
     * @return $this
     */
    public function initFields()
    {
        $this->addEmail('email')->setLabel('Email')->addValidator(new ExistingUser())->required();

        $this->addEmail('code')->setLabel('Code')->addValidator(new ValidCode())->required();

        $this->addSubmit();

        return $this;
    }
}
