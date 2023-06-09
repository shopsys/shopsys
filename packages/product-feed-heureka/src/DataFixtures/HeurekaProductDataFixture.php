<?php

namespace Shopsys\ProductFeed\HeurekaBundle\DataFixtures;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\Plugin\PluginDataFixtureInterface;
use Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomainDataFactoryInterface;
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

    /**
     * @param \Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomainFacade $heurekaProductDomainFacade
     * @param \Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomainDataFactoryInterface $heurekaProductDomainDataFactory
     */
    public function __construct(
        private readonly HeurekaProductDomainFacade $heurekaProductDomainFacade,
        private readonly HeurekaProductDomainDataFactoryInterface $heurekaProductDomainDataFactory,
    ) {
    }

    public function load()
    {
        $heurekaProductDomainData = $this->heurekaProductDomainDataFactory->create();
        $heurekaProductDomainData->domainId = static::DOMAIN_ID_FIRST;
        $heurekaProductDomainData->cpc = Money::create(12);

        $this->heurekaProductDomainFacade->saveHeurekaProductDomain(
            static::PRODUCT_ID_FIRST,
            $heurekaProductDomainData,
        );

        $heurekaProductDomainData = $this->heurekaProductDomainDataFactory->create();
        $heurekaProductDomainData->domainId = static::DOMAIN_ID_SECOND;
        $heurekaProductDomainData->cpc = Money::create(5);

        $this->heurekaProductDomainFacade->saveHeurekaProductDomain(
            static::PRODUCT_ID_FIRST,
            $heurekaProductDomainData,
        );

        $heurekaProductDomainData = $this->heurekaProductDomainDataFactory->create();
        $heurekaProductDomainData->domainId = static::DOMAIN_ID_FIRST;
        $heurekaProductDomainData->cpc = Money::create(3);

        $this->heurekaProductDomainFacade->saveHeurekaProductDomain(
            static::PRODUCT_ID_SECOND,
            $heurekaProductDomainData,
        );

        $heurekaProductDomainData = $this->heurekaProductDomainDataFactory->create();
        $heurekaProductDomainData->domainId = static::DOMAIN_ID_SECOND;
        $heurekaProductDomainData->cpc = Money::create(2);

        $this->heurekaProductDomainFacade->saveHeurekaProductDomain(
            static::PRODUCT_ID_SECOND,
            $heurekaProductDomainData,
        );

        $heurekaProductDomainData = $this->heurekaProductDomainDataFactory->create();
        $heurekaProductDomainData->domainId = static::DOMAIN_ID_FIRST;
        $heurekaProductDomainData->cpc = Money::create(1);

        $this->heurekaProductDomainFacade->saveHeurekaProductDomain(
            static::PRODUCT_ID_THIRD,
            $heurekaProductDomainData,
        );

        $heurekaProductDomainData = $this->heurekaProductDomainDataFactory->create();
        $heurekaProductDomainData->domainId = static::DOMAIN_ID_SECOND;
        $heurekaProductDomainData->cpc = Money::create(1);

        $this->heurekaProductDomainFacade->saveHeurekaProductDomain(
            static::PRODUCT_ID_THIRD,
            $heurekaProductDomainData,
        );

        $heurekaProductDomainData = $this->heurekaProductDomainDataFactory->create();
        $heurekaProductDomainData->domainId = static::DOMAIN_ID_FIRST;
        $heurekaProductDomainData->cpc = Money::create(5);

        $this->heurekaProductDomainFacade->saveHeurekaProductDomain(
            static::PRODUCT_ID_FOURTH,
            $heurekaProductDomainData,
        );

        $heurekaProductDomainData = $this->heurekaProductDomainDataFactory->create();
        $heurekaProductDomainData->domainId = static::DOMAIN_ID_SECOND;
        $heurekaProductDomainData->cpc = Money::create(8);

        $this->heurekaProductDomainFacade->saveHeurekaProductDomain(
            static::PRODUCT_ID_FOURTH,
            $heurekaProductDomainData,
        );

        $heurekaProductDomainData = $this->heurekaProductDomainDataFactory->create();
        $heurekaProductDomainData->domainId = static::DOMAIN_ID_FIRST;
        $heurekaProductDomainData->cpc = Money::create(10);

        $this->heurekaProductDomainFacade->saveHeurekaProductDomain(
            static::PRODUCT_ID_FIFTH,
            $heurekaProductDomainData,
        );

        $heurekaProductDomainData = $this->heurekaProductDomainDataFactory->create();
        $heurekaProductDomainData->domainId = static::DOMAIN_ID_SECOND;
        $heurekaProductDomainData->cpc = Money::create(5);

        $this->heurekaProductDomainFacade->saveHeurekaProductDomain(
            static::PRODUCT_ID_FIFTH,
            $heurekaProductDomainData,
        );
    }
}
