<?php

namespace Pckg\Auth\Form;

use Pckg\Auth\Form\Validator\NonExistingUser;
use Pckg\Auth\Form\Validator\ValidPassword;
use Pckg\Htmlbuilder\Element\Form\Bootstrap;
use Pckg\Htmlbuilder\Element\Form\ResolvesOnRequest;
use Pckg\Htmlbuilder\Validator\Method\Custom;
use Pckg\Htmlbuilder\Validator\Method\Email\Email;

/**
 * Class SignupUser
 *
 * @package Pckg\Auth\Form
 */
class SignupUser extends Bootstrap implements ResolvesOnRequest
{

    /**
     * @return $this
     */
    public function initFields()
    {
        $this->addEmail('email')
            ->setLabel('Email')
            ->addValidator(new NonExistingUser())
            ->addValidator(new Email())
            ->required();
        $this->addPassword('password')
            ->setLabel('Password')
            ->required()
            ->addValidator(new ValidPassword())
            ->addCustomValidator(
                function ($value, Custom $validator) {
                    $validator->setMsg('Passwords does not match');

                    return $value === post('passwordRepeat');

                }
            );
        $this->addPassword('passwordRepeat')->setLabel('Repeat password')->required();

        $this->addSubmit();

        return $this;
    }

}