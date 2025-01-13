<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

class AdministratorDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly Localization $localization,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Administrator\AdministratorData
     */
    protected function createInstance(): AdministratorData
    {
        return new AdministratorData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Administrator\AdministratorData
     */
    public function create(): AdministratorData
    {
        $administratorData = $this->createInstance();

        $administratorData->displayOnlyDomainIds = $this->domain->getAllIds();
        $administratorData->selectedLocale = $this->localization->getDefaultAdminLocale();

        return $administratorData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @return \Shopsys\FrameworkBundle\Model\Administrator\AdministratorData
     */
    public function createFromAdministrator(Administrator $administrator): AdministratorData
    {
        $administratorData = $this->createInstance();
        $this->fillFromAdministrator($administratorData, $administrator);

        return $administratorData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorData $administratorData
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     */
    protected function fillFromAdministrator(AdministratorData $administratorData, Administrator $administrator)
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
