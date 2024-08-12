<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Order\ContentPage\OrderContentPageSettingFacade;

class OrderContentPageSettingValueDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\ContentPage\OrderContentPageSettingFacade $orderContentPageSettingFacade
     */
    public function __construct(
        private readonly OrderContentPageSettingFacade $orderContentPageSettingFacade,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $locale = $domainConfig->getLocale();

            $orderSentText = t('
                <p>
                    Order number {number} has been sent, thank you for your purchase.
                    We will contact you about next order status. <br /><br />
                    <a href="{order_detail_url}">Track</a> the status of your order. <br />
                    {transport_instructions} <br />
                    {payment_instructions}
                </p>
            ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $this->orderContentPageSettingFacade->setOrderSentPageContent($orderSentText, $domainId);

            $paymentSuccessfulText = t('
                <p>
                    Payment for order number {number} has been successful. <br /><br />
                    <a href="{order_detail_url}">Track</a> the status of your order. <br />
                    {transport_instructions}
                </p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $this->orderContentPageSettingFacade->setPaymentSuccessfulPageContent($paymentSuccessfulText, $domainId);

            $paymentFailedText = t('
                <p>
                    Payment for order number {number} has failed. <br /><br />
                    Please contact us to resolve the issue.
                </p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $this->orderContentPageSettingFacade->setPaymentFailedPageContent($paymentFailedText, $domainId);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            SettingValueDataFixture::class,
        ];
    }
}
