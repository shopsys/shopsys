<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator;

use DateTime;
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

    /**
     * @var \DateTime|null
     */
    public $transferIssuesLastSeenDateTime;

    public function __construct()
    {
        $this->roles[] = Roles::ROLE_ADMIN;
        $this->transferIssuesLastSeenDateTime = new DateTime('1970-01-01 00:00:00');
    }
}
