<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\FlashMessage;

use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageService;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(
    name: 'Admin:FlashMessages',
    template: '@ShopsysAdministration/partial/flash_message/all_messages.html.twig',
)]
class FlashMessagesComponent
{
    use DefaultActionTrait;

    public function __construct(
        protected readonly FlashMessageService $flashMessageService,
    ) {
    }

    /**
     * @return string[]
     */
    public function getErrorMessages(): array
    {
        return $this->flashMessageService->getErrorMessages();
    }

    /**
     * @return string[]
     */
    public function getInfoMessages(): array
    {
        return $this->flashMessageService->getInfoMessages();
    }

    /**
     * @return string[]
     */
    public function getSuccessMessages(): array
    {
        return $this->flashMessageService->getSuccessMessages();
    }

    /**
     * @return string[]
     */
    public function getWarningMessages(): array
    {
        return $this->flashMessageService->getWarningMessages();
    }
}
