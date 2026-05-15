<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle\DataFixtures;

use Override;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\Plugin\PluginDataFixtureInterface;
use Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomainDataFactory;
use Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomainFacade;

class HeurekaProductDataFixture implements PluginDataFixtureInterface
{
    protected const DOMAIN_ID_FIRST = 1;
    protected const DOMAIN_ID_SECOND = 2;
    protected const PRODUCT_ID_FIRST = 1;
    protected const PRODUCT_ID_SECOND = 2;
    protected const PRODUCT_ID_THIRD = 3;
    protected const PRODUCT_ID_FOURTH = 4;
    protected const PRODUCT_ID_FIFTH = 5;

    public function __construct(
        protected readonly HeurekaProductDomainFacade $heurekaProductDomainFacade,
        protected readonly HeurekaProductDomainDataFactory $heurekaProductDomainDataFactory,
    ) {
    }

    #[Override]
    public function load(): void
    {
        $firstProductHeurekaDomainData = [];
        $heurekaProductDomainData = $this->heurekaProductDomainDataFactory->create();
        $heurekaProductDomainData->domainId = static::DOMAIN_ID_FIRST;
        $heurekaProductDomainData->cpc = Money::create(12);
        $firstProductHeurekaDomainData[] = $heurekaProductDomainData;

        $heurekaProductDomainData = $this->heurekaProductDomainDataFactory->create();
        $heurekaProductDomainData->domainId = static::DOMAIN_ID_SECOND;
        $heurekaProductDomainData->cpc = Money::create(5);
        $firstProductHeurekaDomainData[] = $heurekaProductDomainData;

        $this->heurekaProductDomainFacade->saveHeurekaProductDomainsForProductId(static::PRODUCT_ID_FIRST, $firstProductHeurekaDomainData);

        $secondProductHeurekaDomainData = [];
        $heurekaProductDomainData = $this->heurekaProductDomainDataFactory->create();
        $heurekaProductDomainData->domainId = static::DOMAIN_ID_FIRST;
        $heurekaProductDomainData->cpc = Money::create(3);
        $secondProductHeurekaDomainData[] = $heurekaProductDomainData;

        $heurekaProductDomainData = $this->heurekaProductDomainDataFactory->create();
        $heurekaProductDomainData->domainId = static::DOMAIN_ID_SECOND;
        $heurekaProductDomainData->cpc = Money::create(2);
        $secondProductHeurekaDomainData[] = $heurekaProductDomainData;

        $this->heurekaProductDomainFacade->saveHeurekaProductDomainsForProductId(static::PRODUCT_ID_SECOND, $secondProductHeurekaDomainData);

        $thirdProductHeurekaDomainData = [];
        $heurekaProductDomainData = $this->heurekaProductDomainDataFactory->create();
        $heurekaProductDomainData->domainId = static::DOMAIN_ID_FIRST;
        $heurekaProductDomainData->cpc = Money::create(1);
        $thirdProductHeurekaDomainData[] = $heurekaProductDomainData;

        $heurekaProductDomainData = $this->heurekaProductDomainDataFactory->create();
        $heurekaProductDomainData->domainId = static::DOMAIN_ID_SECOND;
        $heurekaProductDomainData->cpc = Money::create(1);
        $thirdProductHeurekaDomainData[] = $heurekaProductDomainData;

        $this->heurekaProductDomainFacade->saveHeurekaProductDomainsForProductId(static::PRODUCT_ID_THIRD, $thirdProductHeurekaDomainData);

        $fourthProductHeurekaDomainData = [];
        $heurekaProductDomainData = $this->heurekaProductDomainDataFactory->create();
        $heurekaProductDomainData->domainId = static::DOMAIN_ID_FIRST;
        $heurekaProductDomainData->cpc = Money::create(5);
        $fourthProductHeurekaDomainData[] = $heurekaProductDomainData;

        $heurekaProductDomainData = $this->heurekaProductDomainDataFactory->create();
        $heurekaProductDomainData->domainId = static::DOMAIN_ID_SECOND;
        $heurekaProductDomainData->cpc = Money::create(8);
        $fourthProductHeurekaDomainData[] = $heurekaProductDomainData;

        $this->heurekaProductDomainFacade->saveHeurekaProductDomainsForProductId(static::PRODUCT_ID_FOURTH, $fourthProductHeurekaDomainData);

        $fifthProductHeurekaDomainData = [];
        $heurekaProductDomainData = $this->heurekaProductDomainDataFactory->create();
        $heurekaProductDomainData->domainId = static::DOMAIN_ID_FIRST;
        $heurekaProductDomainData->cpc = Money::create(10);
        $fifthProductHeurekaDomainData[] = $heurekaProductDomainData;

        $heurekaProductDomainData = $this->heurekaProductDomainDataFactory->create();
        $heurekaProductDomainData->domainId = static::DOMAIN_ID_SECOND;
        $heurekaProductDomainData->cpc = Money::create(5);
        $fifthProductHeurekaDomainData[] = $heurekaProductDomainData;

        $this->heurekaProductDomainFacade->saveHeurekaProductDomainsForProductId(static::PRODUCT_ID_FIFTH, $fifthProductHeurekaDomainData);
    }
}
