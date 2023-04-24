<?php

declare(strict_types=1);

namespace App\Model\Administrator;

use Shopsys\FrameworkBundle\Model\Administrator\AdministratorData as BaseAdministratorData;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorDataFactory as BaseAdministratorDataFactory;

/**
 * @method \App\Model\Administrator\AdministratorData create()
 * @method \App\Model\Administrator\AdministratorData createFromAdministrator(\App\Model\Administrator\Administrator $administrator)
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
}
