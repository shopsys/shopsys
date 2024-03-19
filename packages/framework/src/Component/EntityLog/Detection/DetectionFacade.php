<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\Detection;

use Shopsys\FrameworkBundle\Component\EntityLog\Enum\EntityLogSource;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Symfony\Component\Security\Core\Security;

class DetectionFacade
{
    protected ?string $source = null;

    protected ?string $userIdentifier = null;

    /**
     * @param \Symfony\Component\Security\Core\Security $security
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        protected readonly Security $security,
        protected readonly CurrentCustomerUser $currentCustomerUser,
    ) {
    }

    public function setFrontendApiSourceAndUserIdentifier(): void
    {
        $this->source = EntityLogSource::USER;
        $this->userIdentifier = 'notLoggedCustomer';

        $currentCustomer = $this->currentCustomerUser->findCurrentCustomerUser();

        if ($currentCustomer) {
            $this->userIdentifier = $currentCustomer->getUserIdentifier();
        }
    }

    /**
     * @return string
     */
    public function getUserIdentifier(): string
    {
        if ($this->userIdentifier !== null) {
            return $this->userIdentifier;
        }

        $administrator = $this->security->getUser();

        if ($administrator) {
            return $administrator->getUserIdentifier();
        }

        return EntityLogSource::SYSTEM;
    }

    /**
     * @return string
     */
    public function getEntityLogSource(): string
    {
        if ($this->source !== null) {
            return $this->source;
        }

        $administrator = $this->security->getUser();

        if ($administrator) {
            return EntityLogSource::ADMIN;
        }

        return EntityLogSource::SYSTEM;
    }
}
