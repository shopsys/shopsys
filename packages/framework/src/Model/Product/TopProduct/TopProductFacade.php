<?php

namespace Shopsys\FrameworkBundle\Model\Product\TopProduct;

use Doctrine\ORM\EntityManagerInterface;

class TopProductFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductRepository
     */
    protected $topProductRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFactoryInterface
     */
    protected $topProductFactory;

    public function __construct(
        EntityManagerInterface $em,
        TopProductRepository $topProductRepository,
        TopProductFactoryInterface $topProductFactory
    ) {
        $this->em = $em;
        $this->topProductRepository = $topProductRepository;
        $this->topProductFactory = $topProductFactory;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProduct[]
     */
    public function getAll(int $domainId): array
    {
        return $this->topProductRepository->getAll($domainId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getAllOfferedProducts(int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup): array
    {
        return $this->topProductRepository->getOfferedProductsForTopProductsOnDomain($domainId, $pricingGroup);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     */
    public function saveTopProductsForDomain($domainId, array $products): void
    {
        $oldTopProducts = $this->topProductRepository->getAll($domainId);
        foreach ($oldTopProducts as $oldTopProduct) {
            $this->em->remove($oldTopProduct);
        }
        $this->em->flush($oldTopProducts);

        $topProducts = [];
        $position = 1;
        foreach ($products as $product) {
            $topProduct = $this->topProductFactory->create($product, $domainId, $position++);
            $this->em->persist($topProduct);
            $topProducts[] = $topProduct;
        }
        $this->em->flush($topProducts);
    }
}
