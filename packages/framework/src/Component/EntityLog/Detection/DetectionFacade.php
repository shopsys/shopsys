<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\Detection;

use Shopsys\FrameworkBundle\Component\EntityLog\Enum\EntityLogSourceEnum;
use Symfony\Bundle\SecurityBundle\Security;

class DetectionFacade
{
    protected ?string $source = null;

    protected ?string $userIdentifier = null;

    public function __construct(
        protected readonly Security $security,
    ) {
    }

    public function setFrontendApiSourceAndUserIdentifier(): void
    {
        $this->source = EntityLogSourceEnum::USER;
        $this->userIdentifier = 'notLoggedCustomer';

        $user = $this->security->getUser();

        if ($user !== null) {
            $this->userIdentifier = $user->getUserIdentifier();
        }
    }

    public function setSourceAndUserIdentifier(string $source, string $userIdentifier): void
    {
        $this->source = $source;
        $this->userIdentifier = $userIdentifier;
    }

    public function resetSourceAndUserIdentifier(): void
    {
        $this->source = null;
        $this->userIdentifier = null;
    }

    public function getUserIdentifier(): string
    {
        if ($this->userIdentifier !== null) {
            return $this->userIdentifier;
        }

        $administrator = $this->security->getUser();

        if ($administrator) {
            return $administrator->getUserIdentifier();
        }

        return EntityLogSourceEnum::SYSTEM;
    }

    public function getEntityLogSource(): string
    {
        if ($this->source !== null) {
            return $this->source;
        }

        $administrator = $this->security->getUser();

        if ($administrator) {
            return EntityLogSourceEnum::ADMIN;
        }

        return EntityLogSourceEnum::SYSTEM;
    }
}
