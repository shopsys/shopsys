<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator;

class AdministratorDataFactory
{
    public function __construct(
        protected readonly AdministratorLocalizationFacade $administratorLocalizationFacade,
    ) {
    }

    protected function createInstance(): AdministratorData
    {
        return new AdministratorData();
    }

    public function create(): AdministratorData
    {
        $administratorData = $this->createInstance();

        $administratorData->selectedLocale = $this->administratorLocalizationFacade->getDefaultAdminLocale();

        return $administratorData;
    }

    public function createFromAdministrator(Administrator $administrator): AdministratorData
    {
        $administratorData = $this->createInstance();
        $this->fillFromAdministrator($administratorData, $administrator);

        return $administratorData;
    }

    protected function fillFromAdministrator(AdministratorData $administratorData, Administrator $administrator): void
    {
        $administratorData->email = $administrator->getEmail();
        $administratorData->realName = $administrator->getRealName();
        $administratorData->username = $administrator->getUsername();
        $administratorData->roles = $administrator->getRoles();
        $administratorData->transferIssuesLastSeenDateTime = $administrator->getTransferIssuesLastSeenDateTime();
        $administratorData->roleGroup = $administrator->getRoleGroup();
        $administratorData->displayOnlyDomainIds = $administrator->getDisplayOnlyDomainIds();

        if ($administrator->getRoleGroup() !== null) {
            $administratorData->roles = [];
        }
    }
}
