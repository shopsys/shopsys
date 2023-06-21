<?php

declare(strict_types=1);

namespace App\Model\Administrator;

use App\Model\Administrator\RoleGroup\AdministratorRoleGroup;
use DateTime;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorData as BaseAdministratorData;

class AdministratorData extends BaseAdministratorData
{
    /**
     * @var \DateTime|null
     */
    public $transferIssuesLastSeenDateTime;

    /**
     * @var \App\Model\Administrator\RoleGroup\AdministratorRoleGroup|null
     */
    public ?AdministratorRoleGroup $roleGroup;

    public function __construct()
    {
        parent::__construct();

        $this->transferIssuesLastSeenDateTime = new DateTime('1970-01-01 00:00:00');
        $this->roleGroup = null;
    }
}
