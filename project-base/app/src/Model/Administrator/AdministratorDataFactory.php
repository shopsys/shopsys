<?php

declare(strict_types=1);

namespace App\Model\Administrator;

use Shopsys\FrameworkBundle\Model\Administrator\Administrator as BaseAdministrator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorData as BaseAdministratorData;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorDataFactory as BaseAdministratorDataFactory;

/**
 * @method \App\Model\Administrator\AdministratorData create()
 * @method fillFromAdministrator(\App\Model\Administrator\AdministratorData $administratorData, \App\Model\Administrator\Administrator $administrator)
 */
class AdministratorDataFactory extends BaseAdministratorDataFactory
{
    /**
     * @return \App\Model\Administrator\AdministratorData
     */
    public function createInstance(): BaseAdministratorData
    {
        return new AdministratorData();
    }

    /**
     * @param \App\Model\Administrator\Administrator $administrator
     * @return \App\Model\Administrator\AdministratorData
     */
    public function createFromAdministrator(BaseAdministrator $administrator): BaseAdministratorData
    {
        $administratorData = new AdministratorData();
        $this->fillFromAdministrator($administratorData, $administrator);
        $administratorData->transferIssuesLastSeenDateTime = $administrator->getTransferIssuesLastSeenDateTime();
        $administratorData->roleGroup = $administrator->getRoleGroup();

        if ($administrator->getRoleGroup() !== null) {
            $administratorData->roles = [];
        }

        return $administratorData;
    }
}
