<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Shopsys\FrameworkBundle\Model\Administrator\AdministratorLocalizationFacade;

interface AdministratorLocalizationAwareInterface
{
    public function setAdministratorLocalizationFacade(
        AdministratorLocalizationFacade $administratorLocalizationFacade,
    ): void;
}
