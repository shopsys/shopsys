<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Order\Order;
use DateTimeImmutable;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;

class OrderWithdrawalRequestDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public function __construct(
        protected readonly WithdrawalRequestFacade $withdrawalRequestFacade,
        protected readonly WithdrawalRequestDataFactory $withdrawalRequestDataFactory,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $orderReference = $this->getReferenceForDomain(OrderDataFixture::ORDER_WITH_WITHDRAWAL_REQUEST, $domainConfig->getId(), Order::class);

            $withdrawalRequestData = $this->withdrawalRequestDataFactory->create();
            $withdrawalRequestData->firstName = 'Adam';
            $withdrawalRequestData->lastName = 'Bořič';
            $withdrawalRequestData->telephone = new PhoneData('CZ', '+420', '755496328');
            $withdrawalRequestData->email = 'no-reply@shopsys.com';
            $withdrawalRequestData->note = t('Product does not match description, I want to return the entire order.', domain: Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, locale: $domainConfig->getLocale());
            $withdrawalRequestData->requestedAt = new DateTimeImmutable('now -1 day')->setTime(22, 55, 15);
            $withdrawalRequestData->confirmed = true;

            $this->withdrawalRequestFacade->createOnly($orderReference, $withdrawalRequestData);
        }
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDependencies(): array
    {
        return [OrderDataFixture::class];
    }
}
