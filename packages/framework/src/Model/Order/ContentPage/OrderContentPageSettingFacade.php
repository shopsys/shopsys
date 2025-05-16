<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\ContentPage;

use Shopsys\FrameworkBundle\Component\Setting\Setting;

class OrderContentPageSettingFacade
{
    protected const string ORDER_SENT_PAGE_CONTENT = 'orderSubmittedText';
    protected const string PAYMENT_SUCCESSFUL_PAGE_CONTENT = 'paymentSuccessfulText';
    protected const string PAYMENT_FAILED_PAGE_CONTENT = 'paymentFailedText';
    protected const string PAYMENT_IN_PROCESS_PAGE_CONTENT = 'paymentInProcessText';

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
     * @param int $domainId
     * @return string
     */
    public function getPaymentSuccessfulPageContent(int $domainId): string
    {
        return $this->setting->getForDomain(static::PAYMENT_SUCCESSFUL_PAGE_CONTENT, $domainId);
    }

    /**
     * @param int $domainId
     * @return string
     */
    public function getPaymentFailedPageContent(int $domainId): string
    {
        return $this->setting->getForDomain(static::PAYMENT_FAILED_PAGE_CONTENT, $domainId);
    }

    /**
     * @param int $domainId
     * @return string
     */
    public function getPaymentInProcessPageContent(int $domainId): string
    {
        return $this->setting->getForDomain(static::PAYMENT_IN_PROCESS_PAGE_CONTENT, $domainId);
    }

    /**
     * @param string $content
     * @param int $domainId
     */
    public function setOrderSentPageContent(string $content, int $domainId): void
    {
        $this->setting->setForDomain(static::ORDER_SENT_PAGE_CONTENT, $content, $domainId);
    }

    /**
     * @param string $content
     * @param int $domainId
     */
    public function setPaymentSuccessfulPageContent(string $content, int $domainId): void
    {
        $this->setting->setForDomain(static::PAYMENT_SUCCESSFUL_PAGE_CONTENT, $content, $domainId);
    }

    /**
     * @param string $content
     * @param int $domainId
     */
    public function setPaymentFailedPageContent(string $content, int $domainId): void
    {
        $this->setting->setForDomain(static::PAYMENT_FAILED_PAGE_CONTENT, $content, $domainId);
    }

    /**
     * @param string $content
     * @param int $domainId
     */
    public function setPaymentInProcessPageContent(string $content, int $domainId): void
    {
        $this->setting->setForDomain(static::PAYMENT_IN_PROCESS_PAGE_CONTENT, $content, $domainId);
    }
}
