<?php namespace Pckg\Auth\Record;

use Pckg\Auth\Entity\Logins;
use Pckg\Database\Record;

/**
 * Class Login
 * @package Pckg\Auth\Record
 */
class Login extends Record
{

    /**
     * @var string
     */
    protected $entity = Logins::class;

}