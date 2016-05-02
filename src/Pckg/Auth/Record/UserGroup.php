<?php

namespace Pckg\Auth\Record;

use Pckg\Database\Record;
use Pckg\Auth\Entity\UserGroups;

class UserGroup extends Record {

    protected $entity = UserGroups::class;

}