<?php namespace Pckg\Auth\Form;

use Pckg\Auth\Form\Validator\ExistingUser;
use Pckg\Auth\Form\Validator\ValidCode;
use Pckg\Auth\Form\Validator\ValidPassword;
use Pckg\Htmlbuilder\Element\Form\Bootstrap;
use Pckg\Htmlbuilder\Element\Form\ResolvesOnRequest;
use Pckg\Htmlbuilder\Validator\Method\Custom;

/**
 * Class ResetPassword
 *
 * @package Pckg\Auth\Form
 */
class ResetPassword extends Bootstrap implements ResolvesOnRequest
{

    /**
     * @return $this
     */
    public function initFields()
    {
        $this->addEmail('email')->setLabel('Email')->addValidator(new ExistingUser())->required();

        $this->addEmail('code')->setLabel('Code')->addValidator(new ValidCode())->required();

        $this->addPassword('password')->setLabel('Password')->addValidator(new ValidPassword())->required();

        $this->addPassword('passwordRepeat')
            ->setLabel('Password')
            ->addValidator(new ValidPassword())
            ->addValidator(
                new Custom(
                    function ($value, Custom $validator) {
                        $validator->setMsg('Passwords don\'t match');

                        return post('password') === post('passwordRepeat');
                    }
                )
            )
            ->required();

        $this->addSubmit();

        return $this;
    }

}