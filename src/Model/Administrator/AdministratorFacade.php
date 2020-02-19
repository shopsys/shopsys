<?php

declare(strict_types=1);

namespace App\Model\Administrator;

use DateTime;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade as BaseAdministratorFacade;

/**
 * @method \App\Model\Administrator\Administrator create(\App\Model\Administrator\AdministratorData $administratorData)
 * @method \App\Model\Administrator\Administrator edit(int $administratorId, \App\Model\Administrator\AdministratorData $administratorData)
 * @method checkUsername(\App\Model\Administrator\Administrator $administrator, string $username)
 * @method setPassword(\App\Model\Administrator\Administrator $administrator, string $password)
 * @method checkForDelete(\App\Model\Administrator\Administrator $administrator)
 * @method \App\Model\Administrator\Administrator getById(int $administratorId)
 * @method setRolesChangedNow(\App\Model\Administrator\Administrator $administrator)
 */
class AdministratorFacade extends BaseAdministratorFacade
{
    /**
     * @param \App\Model\Administrator\Administrator $administrator
     */
    public function setAdministratorTransferIssuesLastSeenDateTime(Administrator $administrator): void
    {
        $administrator->setTransferIssuesLastSeenDateTime(new DateTime());
        $this->em->flush();
    }
}
