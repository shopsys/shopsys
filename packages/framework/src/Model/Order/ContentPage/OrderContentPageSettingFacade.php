<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\ContentPage;

use Shopsys\FrameworkBundle\Component\Setting\Setting;

class OrderContentPageSettingFacade
{
    protected const ORDER_SENT_PAGE_CONTENT = 'orderSubmittedText';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     */
    public function __construct(
        protected readonly Setting $setting,
    ) {
    }

    /**
     * @param int $domainId
     * @return string
     */
    public function getOrderSentPageContent(int $domainId): string
    {
        return $this->setting->getForDomain(static::ORDER_SENT_PAGE_CONTENT, $domainId);
    }

    /**
     * @param string $content
     * @param int $domainId
     */
    public function setOrderSentPageContent(string $content, int $domainId): void
    {
        $this->setting->setForDomain(static::ORDER_SENT_PAGE_CONTENT, $content, $domainId);
    }
}
