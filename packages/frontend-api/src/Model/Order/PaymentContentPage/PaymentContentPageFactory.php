<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order\PaymentContentPage;

class PaymentContentPageFactory
{
    public function createSuccessful(string $content): PaymentContentPage
    {
        return new PaymentContentPage(
            $content,
            PaymentContentPageStatusEnum::STATUS_SUCCESSFUL,
        );
    }

    public function createFailed(string $content): PaymentContentPage
    {
        return new PaymentContentPage(
            $content,
            PaymentContentPageStatusEnum::STATUS_FAILED,
        );
    }

    public function createInProcess(string $content): PaymentContentPage
    {
        return new PaymentContentPage(
            $content,
            PaymentContentPageStatusEnum::STATUS_IN_PROCESS,
        );
    }
}
