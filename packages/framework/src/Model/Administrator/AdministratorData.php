<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Shopsys\FrameworkBundle\Model\Security\Roles;

class AdministratorData
{
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
        $this->roles[] = Roles::ROLE_ADMIN;
    }
}
