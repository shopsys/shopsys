<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\Processing\Preloader;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInput;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\Preloader\OrderInputPreloaderFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPriceFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceRepository;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;

class OrderInputPreloaderFacadeTest extends TestCase
{
    public function testProductDataIsPreloadedOncePerProductSetWithinRequest(): void
    {
        $productRepositoryMock = $this->createMock(ProductRepository::class);
        $productRepositoryMock->expects($this->once())->method('preloadWithDomainsVatsAndTranslationsByIds')->with([1, 2]);
        $orderInputPreloaderFacade = $this->createOrderInputPreloaderFacade($productRepositoryMock);

        $orderInputPreloaderFacade->preload($this->createOrderInput([1, 2]));
        $orderInputPreloaderFacade->preload($this->createOrderInput([2, 1]));
    }

    public function testProductDataIsPreloadedAgainForAnotherProductSet(): void
    {
        $productRepositoryMock = $this->createMock(ProductRepository::class);
        $productRepositoryMock->expects($this->exactly(2))->method('preloadWithDomainsVatsAndTranslationsByIds');
        $orderInputPreloaderFacade = $this->createOrderInputPreloaderFacade($productRepositoryMock);

        $orderInputPreloaderFacade->preload($this->createOrderInput([1]));
        $orderInputPreloaderFacade->preload($this->createOrderInput([1, 2]));
    }

    public function testNothingIsPreloadedForOrderInputWithoutProducts(): void
    {
        $productRepositoryMock = $this->createMock(ProductRepository::class);
        $productRepositoryMock->expects($this->never())->method('preloadWithDomainsVatsAndTranslationsByIds');
        $orderInputPreloaderFacade = $this->createOrderInputPreloaderFacade($productRepositoryMock);

        $orderInputPreloaderFacade->preload($this->createOrderInput([]));
    }

    private function createOrderInputPreloaderFacade(ProductRepository $productRepository): OrderInputPreloaderFacade
    {
        return new OrderInputPreloaderFacade(
            $productRepository,
            $this->createStub(ProductManualInputPriceRepository::class),
            $this->createStub(SpecialPriceFacade::class),
            $this->createStub(ProductVisibilityFacade::class),
            $this->createStub(PricingGroupSettingFacade::class),
            new InMemoryCache(),
        );
    }

    /**
     * @param int[] $productIds
     */
    private function createOrderInput(array $productIds): OrderInput
    {
        $domainConfigStub = $this->createStub(DomainConfig::class);
        $domainConfigStub->method('getId')->willReturn(1);
        $orderInput = new OrderInputFactory()->create($domainConfigStub);

        foreach ($productIds as $productId) {
            $productStub = $this->createStub(Product::class);
            $productStub->method('getId')->willReturn($productId);
            $orderInput->addProduct($productStub, 1);
        }

        return $orderInput;
    }
}
