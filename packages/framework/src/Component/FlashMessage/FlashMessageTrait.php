<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\FlashMessage;

/**
 * @property \Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageService $flashMessageService
 */
trait FlashMessageTrait
{
    public function addSuccessFlashTwig(string $template, array $parameters = []): void
    {
        $this->flashMessageService->addSuccessFlashTwig($template, $parameters);
    }

    public function addErrorFlashTwig(string $template, array $parameters = []): void
    {
        $this->flashMessageService->addErrorFlashTwig($template, $parameters);
    }

    public function addInfoFlashTwig(string $template, array $parameters = []): void
    {
        $this->flashMessageService->addInfoFlashTwig($template, $parameters);
    }

    public function addErrorFlash(string $message): void
    {
        $this->flashMessageService->addErrorFlash($message);
    }

    public function addInfoFlash(string $message): void
    {
        $this->flashMessageService->addInfoFlash($message);
    }

    public function addSuccessFlash(string $message): void
    {
        $this->flashMessageService->addSuccessFlash($message);
    }

    public function addWarningFlash(string $message): void
    {
        $this->flashMessageService->addWarningFlash($message);
    }

    public function isFlashMessageBagEmpty(): bool
    {
        return $this->flashMessageService->isFlashMessageBagEmpty();
    }

    public function hasErrorMessages(): bool
    {
        return $this->flashMessageService->hasErrorMessages();
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
