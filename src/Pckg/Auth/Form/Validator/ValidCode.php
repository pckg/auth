<?php namespace Pckg\Auth\Form\Validator;

use Pckg\Auth\Entity\UserPasswordResets;
use Pckg\Htmlbuilder\Validator\AbstractValidator;

class ValidCode extends AbstractValidator
{

    protected $recursive = false;

    protected $msg = 'Code is not valid or is expired';

    public function validate($value)
    {
        $code = (new UserPasswordResets())->joinUser()
            ->where('code', $value)
            ->where('created_at', date('Y-m-d H:i:s', strtotime('-1day')), '>=')
            ->where('used_at', null)
            ->where('email', post('email', null))
            ->one();

        if (!$code) {
            return false;
        }

        return true;
    }

}