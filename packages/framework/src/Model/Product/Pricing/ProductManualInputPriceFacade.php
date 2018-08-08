<?php

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductManualInputPriceFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceRepository
     */
    protected $productManualInputPriceRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceService
     */
    protected $productManualInputPriceService;

    public function __construct(
        EntityManagerInterface $em,
        ProductManualInputPriceRepository $productManualInputPriceRepository,
        ProductManualInputPriceService $productManualInputPriceService
    ) {
        $this->em = $em;
        $this->productManualInputPriceRepository = $productManualInputPriceRepository;
        $this->productManualInputPriceService = $productManualInputPriceService;
    }
    
    public function refresh(Product $product, PricingGroup $pricingGroup, string $inputPrice): void
    {
        $manualInputPrice = $this->productManualInputPriceRepository->findByProductAndPricingGroup($product, $pricingGroup);
        $refreshedProductManualInputPrice = $this->productManualInputPriceService->refresh(
            $product,
            $pricingGroup,
            $inputPrice,
            $manualInputPrice
        );
        $this->em->persist($refreshedProductManualInputPrice);
        $this->em->flush($refreshedProductManualInputPrice);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPrice[]
     */
    public function getAllByProduct(Product $product): array
    {
        return $this->productManualInputPriceRepository->getByProduct($product);
    }

    public function deleteByProduct(Product $product): void
    {
        $manualInputPrices = $this->productManualInputPriceRepository->getByProduct($product);
        foreach ($manualInputPrices as $manualInputPrice) {
            $this->em->remove($manualInputPrice);
        }
        $this->em->flush($manualInputPrices);
    }
}
