<?php

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Shopsys\FrameworkBundle\Model\Security\Roles;

class AdministratorData
{
    /**
     * @var bool
     */
    public $superadmin;

    /**
     * @var string|null
     */
    public $username;

    /**
     * @var string|null
     */
    public $realName;

    /**
     * @var string|null
     */
    public $password;

    /**
     * @var string|null
     */
    public $email;

    /**
     * @var string[]
     */
    public $roles;

    public function __construct()
    {
        $this->superadmin = false;
        $this->roles[] = Roles::ROLE_ADMIN;
    }
}
