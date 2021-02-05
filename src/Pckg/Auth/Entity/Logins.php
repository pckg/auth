<?php namespace Pckg\Auth\Entity;

use Pckg\Auth\Record\Login;
use Pckg\Database\Entity;

/**
 * Class Logins
 * @package Pckg\Auth\Entity
 */
class Logins extends Entity
{

    /**
     * @var string
     */
    protected $record = Login::class;
}