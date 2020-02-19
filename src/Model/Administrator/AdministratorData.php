<?php

declare(strict_types=1);

namespace App\Model\Administrator;

use DateTime;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorData as BaseAdministratorData;

class AdministratorData extends BaseAdministratorData
{
    /**
     * @var \DateTime|null
     */
    public $transferIssuesLastSeenDateTime;

    public function __construct()
    {
        parent::__construct();
        $this->transferIssuesLastSeenDateTime = new DateTime('1970-01-01 00:00:00');
    }
}
