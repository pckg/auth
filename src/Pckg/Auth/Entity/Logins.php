<?php namespace Pckg\Auth\Entity;

use Pckg\Auth\Record\Login;
use Pckg\Database\Entity;

class Logins extends Entity
{

    protected $record = Login::class;
}