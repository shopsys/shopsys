<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order\PaymentContentPage;

class PaymentContentPageFactory
{
    /**
     * @param string $content
     * @return \Shopsys\FrontendApiBundle\Model\Order\PaymentContentPage\PaymentContentPage
     */
    public function createSuccessful(string $content): PaymentContentPage
    {
        return new PaymentContentPage(
            $content,
            PaymentContentPageStatusEnum::STATUS_SUCCESSFUL,
        );
    }

    /**
     * @param string $content
     * @return \Shopsys\FrontendApiBundle\Model\Order\PaymentContentPage\PaymentContentPage
     */
    public function createFailed(string $content): PaymentContentPage
    {
        return new PaymentContentPage(
            $content,
            PaymentContentPageStatusEnum::STATUS_FAILED,
        );
    }

    /**
     * @param string $content
     * @return \Shopsys\FrontendApiBundle\Model\Order\PaymentContentPage\PaymentContentPage
     */
    public function createInProcess(string $content): PaymentContentPage
    {
        return new PaymentContentPage(
            $content,
            PaymentContentPageStatusEnum::STATUS_IN_PROCESS,
        );
    }
}
