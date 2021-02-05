<?php

namespace Pckg\Auth\Form\Validator;

use Pckg\Auth\Entity\UserPasswordResets;
use Pckg\Htmlbuilder\Validator\AbstractValidator;

/**
 * Class ValidCode
 *
 * @package Pckg\Auth\Form\Validator
 */
class ValidCode extends AbstractValidator
{

    /**
     * @var bool
     */
    protected $recursive = false;

    /**
     * @var string
     */
    protected $msg = 'Code is not valid or is expired';

    /**
     * @param  $value
     * @return bool
     */
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
