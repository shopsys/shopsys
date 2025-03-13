<?php

declare(strict_types=1);

namespace App\Model\Administrator;

use Override;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorData as BaseAdministratorData;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorDataFactory as BaseAdministratorDataFactory;

/**
 * @method \App\Model\Administrator\AdministratorData create()
 * @method fillFromAdministrator(\App\Model\Administrator\AdministratorData $administratorData, \App\Model\Administrator\Administrator $administrator)
 * @method \App\Model\Administrator\AdministratorData createFromAdministrator(\App\Model\Administrator\Administrator $administrator)
 */
class AdministratorDataFactory extends BaseAdministratorDataFactory
{
    /**
     * @return \App\Model\Administrator\AdministratorData
     */
    #[Override]
    public function createInstance(): BaseAdministratorData
    {
        return new AdministratorData();
    }
}
