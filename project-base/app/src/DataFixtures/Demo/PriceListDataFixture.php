<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

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
            $this->priceListFacade->create($priceListData);

            $priceListData = $this->priceListDataFactory->create();
            $priceListData->name = t('Blue wednesday', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainConfig->getLocale());
            $priceListData->domainId = $domainConfig->getId();
            $this->priceListFacade->create($priceListData);

            $priceListData = $this->priceListDataFactory->create();
            $priceListData->name = t('Items on sale', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainConfig->getLocale());
            $priceListData->domainId = $domainConfig->getId();
            $this->priceListFacade->create($priceListData);

            $priceListData = $this->priceListDataFactory->create();
            $priceListData->name = t('Promoted products', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainConfig->getLocale());
            $priceListData->domainId = $domainConfig->getId();
            $this->priceListFacade->create($priceListData);
        }
    }
}
