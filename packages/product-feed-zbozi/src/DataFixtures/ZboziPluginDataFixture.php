<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle\DataFixtures;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\Plugin\PluginDataFixtureInterface;
use Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomainDataFactoryInterface;
use Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomainFacade;

class ZboziPluginDataFixture implements PluginDataFixtureInterface
{
    protected const DOMAIN_ID_FIRST = 1;
    protected const DOMAIN_ID_SECOND = 2;
    protected const PRODUCT_ID_FIRST = 1;
    protected const PRODUCT_ID_SECOND = 2;
    protected const PRODUCT_ID_THIRD = 3;
    protected const PRODUCT_ID_FOURTH = 4;
    protected const PRODUCT_ID_FIFTH = 5;

    /**
     * @param \Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomainFacade $zboziProductDomainFacade
     * @param \Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomainDataFactoryInterface $zboziProductDomainDataFactory
     */
    public function __construct(
        private readonly ZboziProductDomainFacade $zboziProductDomainFacade,
        private readonly ZboziProductDomainDataFactoryInterface $zboziProductDomainDataFactory,
    ) {
    }

    public function load()
    {
        $firstProductZboziDomainData = [];
        $zboziProductDomainData = $this->zboziProductDomainDataFactory->create();
        $zboziProductDomainData->domainId = static::DOMAIN_ID_FIRST;
        $zboziProductDomainData->cpc = Money::create(15);
        $zboziProductDomainData->cpcSearch = Money::create(8);
        $zboziProductDomainData->show = true;
        $firstProductZboziDomainData[] = $zboziProductDomainData;

        $zboziProductDomainData = $this->zboziProductDomainDataFactory->create();
        $zboziProductDomainData->domainId = static::DOMAIN_ID_SECOND;
        $zboziProductDomainData->cpc = Money::create(12);
        $zboziProductDomainData->cpcSearch = Money::create(15);
        $zboziProductDomainData->show = true;
        $firstProductZboziDomainData[] = $zboziProductDomainData;

        $this->zboziProductDomainFacade->saveZboziProductDomainsForProductId(static::PRODUCT_ID_FIRST, $firstProductZboziDomainData);

        $secondProductZboziDomainData = [];
        $zboziProductDomainData = $this->zboziProductDomainDataFactory->create();
        $zboziProductDomainData->domainId = static::DOMAIN_ID_FIRST;
        $zboziProductDomainData->cpc = Money::create(5);
        $zboziProductDomainData->cpcSearch = Money::create(3);
        $zboziProductDomainData->show = false;
        $secondProductZboziDomainData[] = $zboziProductDomainData;

        $zboziProductDomainData = $this->zboziProductDomainDataFactory->create();
        $zboziProductDomainData->domainId = static::DOMAIN_ID_SECOND;
        $zboziProductDomainData->cpc = Money::create(20);
        $zboziProductDomainData->cpcSearch = Money::create(5);
        $zboziProductDomainData->show = true;
        $secondProductZboziDomainData[] = $zboziProductDomainData;

        $this->zboziProductDomainFacade->saveZboziProductDomainsForProductId(static::PRODUCT_ID_SECOND, $secondProductZboziDomainData);

        $thirdProductZboziDomainData = [];
        $zboziProductDomainData = $this->zboziProductDomainDataFactory->create();
        $zboziProductDomainData->domainId = static::DOMAIN_ID_FIRST;
        $zboziProductDomainData->cpc = Money::create(10);
        $zboziProductDomainData->cpcSearch = Money::create(5);
        $zboziProductDomainData->show = false;
        $thirdProductZboziDomainData[] = $zboziProductDomainData;

        $zboziProductDomainData = $this->zboziProductDomainDataFactory->create();
        $zboziProductDomainData->domainId = static::DOMAIN_ID_SECOND;
        $zboziProductDomainData->cpc = Money::create(15);
        $zboziProductDomainData->cpcSearch = Money::create(7);
        $zboziProductDomainData->show = false;
        $thirdProductZboziDomainData[] = $zboziProductDomainData;

        $this->zboziProductDomainFacade->saveZboziProductDomainsForProductId(static::PRODUCT_ID_THIRD, $thirdProductZboziDomainData);

        $fourthProductZboziDomainData = [];
        $zboziProductDomainData = $this->zboziProductDomainDataFactory->create();
        $zboziProductDomainData->domainId = static::DOMAIN_ID_FIRST;
        $zboziProductDomainData->cpc = Money::create(9);
        $zboziProductDomainData->cpcSearch = Money::create(8);
        $zboziProductDomainData->show = true;
        $fourthProductZboziDomainData[] = $zboziProductDomainData;

        $zboziProductDomainData = $this->zboziProductDomainDataFactory->create();
        $zboziProductDomainData->domainId = static::DOMAIN_ID_SECOND;
        $zboziProductDomainData->cpc = Money::create(4);
        $zboziProductDomainData->cpcSearch = Money::create(3);
        $zboziProductDomainData->show = true;
        $fourthProductZboziDomainData[] = $zboziProductDomainData;

        $this->zboziProductDomainFacade->saveZboziProductDomainsForProductId(static::PRODUCT_ID_FOURTH, $fourthProductZboziDomainData);

        $fifthProductZboziDomainData = [];
        $zboziProductDomainData = $this->zboziProductDomainDataFactory->create();
        $zboziProductDomainData->domainId = static::DOMAIN_ID_FIRST;
        $zboziProductDomainData->cpc = Money::create(4);
        $zboziProductDomainData->cpcSearch = Money::create(2);
        $zboziProductDomainData->show = true;
        $fifthProductZboziDomainData[] = $zboziProductDomainData;

        $zboziProductDomainData = $this->zboziProductDomainDataFactory->create();
        $zboziProductDomainData->domainId = static::DOMAIN_ID_SECOND;
        $zboziProductDomainData->cpc = Money::create(5);
        $zboziProductDomainData->cpcSearch = Money::create(6);
        $zboziProductDomainData->show = false;
        $fifthProductZboziDomainData[] = $zboziProductDomainData;

        $this->zboziProductDomainFacade->saveZboziProductDomainsForProductId(static::PRODUCT_ID_FIFTH, $fifthProductZboziDomainData);
    }
}
