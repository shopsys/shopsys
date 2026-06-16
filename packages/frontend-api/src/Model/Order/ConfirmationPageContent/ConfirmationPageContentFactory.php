<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order\ConfirmationPageContent;

class ConfirmationPageContentFactory
{
    protected function createInstance(string $content, string $status): ConfirmationPageContent
    {
        return new ConfirmationPageContent(
            $content,
            $status,
        );
    }

    public function createSuccessful(string $content): ConfirmationPageContent
    {
        return $this->createInstance(
            $content,
            ConfirmationPageContentStatusEnum::STATUS_SUCCESSFUL,
        );
    }

    public function createFailed(string $content): ConfirmationPageContent
    {
        return $this->createInstance(
            $content,
            ConfirmationPageContentStatusEnum::STATUS_FAILED,
        );
    }

    public function createInProcess(string $content): ConfirmationPageContent
    {
        return $this->createInstance(
            $content,
            ConfirmationPageContentStatusEnum::STATUS_IN_PROCESS,
        );
    }
}
