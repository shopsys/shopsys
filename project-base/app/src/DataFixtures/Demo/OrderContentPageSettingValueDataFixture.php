<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Order\ContentPage\OrderContentPageSettingFacade;

class OrderContentPageSettingValueDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Order\ContentPage\OrderContentPageSettingFacade $orderContentPageSettingFacade
     */
    public function __construct(
        private readonly Domain $domain,
        private readonly OrderContentPageSettingFacade $orderContentPageSettingFacade,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domain->getAll() as $domainConfig) {
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
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): iterable
    {
        return [
            SettingValueDataFixture::class,
        ];
    }
}
