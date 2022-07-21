<?php

namespace Tests\FrameworkBundle\Unit\Model\Product;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ProductVisibilityFacadeTest extends TestCase
{
    public function testOnKernelResponseRecalc(): void
    {
        $productVisibilityRepositoryMock = $this->createMock(ProductVisibilityRepository::class);
        $productVisibilityRepositoryMock
            ->expects($this->once())
            ->method('refreshProductsVisibility')
            ->with($this->equalTo(true));

        $productVisibilityFacade = new ProductVisibilityFacade($productVisibilityRepositoryMock);
        $productVisibilityFacade->refreshProductsVisibilityForMarkedDelayed();

        $responseEvent = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MASTER_REQUEST,
            new Response()
        );

        $productVisibilityFacade->onKernelResponse($responseEvent);
    }

    public function testOnKernelResponseNoRecalc(): void
    {
        $productVisibilityRepositoryMock = $this->createMock(ProductVisibilityRepository::class);
        $productVisibilityRepositoryMock->expects($this->never())->method('refreshProductsVisibility');

        $productVisibilityFacade = new ProductVisibilityFacade($productVisibilityRepositoryMock);

        $responseEvent = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MASTER_REQUEST,
            new Response()
        );

        $productVisibilityFacade->onKernelResponse($responseEvent);
    }

    public function testRefreshProductsVisibility(): void
    {
        $productVisibilityRepositoryMock = $this->createMock(ProductVisibilityRepository::class);
        $productVisibilityRepositoryMock->expects($this->once())->method('refreshProductsVisibility');

        $productVisibilityFacade = new ProductVisibilityFacade($productVisibilityRepositoryMock);
        $productVisibilityFacade->refreshProductsVisibility();
    }

    public function testRefreshProductsVisibilityForMarked(): void
    {
        $productVisibilityRepositoryMock = $this->createMock(ProductVisibilityRepository::class);
        $productVisibilityRepositoryMock
            ->expects($this->once())
            ->method('refreshProductsVisibility')
            ->with($this->equalTo(true));

        $productVisibilityFacade = new ProductVisibilityFacade($productVisibilityRepositoryMock);
        $productVisibilityFacade->refreshProductsVisibilityForMarked();
    }
}
