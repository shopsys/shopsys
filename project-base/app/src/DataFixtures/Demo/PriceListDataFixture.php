<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\PriceList\PriceListDataFactory;
use Shopsys\FrameworkBundle\Model\PriceList\PriceListFacade;

class PriceListDataFixture extends AbstractReferenceFixture
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListFacade $priceListFacade
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListDataFactory $priceListDataFactory
     */
    public function __construct(
        protected readonly PriceListFacade $priceListFacade,
        protected readonly PriceListDataFactory $priceListDataFactory,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $priceListData = $this->priceListDataFactory->create();
            $priceListData->name = t('Special offers', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainConfig->getLocale());
            $priceListData->domainId = $domainConfig->getId();
            $priceListData->validFrom = new DateTimeImmutable('2023-01-10 08:30:00');
            $priceListData->validTo = new DateTimeImmutable('2084-01-10 08:30:00');
            $this->priceListFacade->create($priceListData);

            $priceListData = $this->priceListDataFactory->create();
            $priceListData->name = t('Blue wednesday', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainConfig->getLocale());
            $priceListData->domainId = $domainConfig->getId();
            $priceListData->validFrom = new DateTimeImmutable('2023-11-10 00:00:00');
            $priceListData->validTo = new DateTimeImmutable('2023-11-10 23:59:59');
            $this->priceListFacade->create($priceListData);

            $priceListData = $this->priceListDataFactory->create();
            $priceListData->name = t('Items on sale', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainConfig->getLocale());
            $priceListData->domainId = $domainConfig->getId();
            $priceListData->validFrom = new DateTimeImmutable('2023-02-12 06:20:00');
            $priceListData->validTo = new DateTimeImmutable('2084-05-10 08:30:00');
            $this->priceListFacade->create($priceListData);

            $priceListData = $this->priceListDataFactory->create();
            $priceListData->name = t('Promoted products', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainConfig->getLocale());
            $priceListData->domainId = $domainConfig->getId();
            $priceListData->validFrom = new DateTimeImmutable('2083-10-15 00:20:00');
            $priceListData->validTo = new DateTimeImmutable('2084-10-15 06:30:00');
            $this->priceListFacade->create($priceListData);
        }
    }
}
