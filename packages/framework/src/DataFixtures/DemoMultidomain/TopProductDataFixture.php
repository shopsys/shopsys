<?php

namespace Shopsys\FrameworkBundle\DataFixtures\DemoMultidomain;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\DataFixtures\Demo\ProductDataFixture as DemoProductDataFixture;
use Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade;

class TopProductDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade
     */
    private $topProductFacade;

    public function __construct(TopProductFacade $topProductFacade)
    {
        $this->topProductFacade = $topProductFacade;
    }

    public function load(ObjectManager $manager): void
    {
        $topProductReferenceNamesOnDomain2 = [
            DemoProductDataFixture::PRODUCT_PREFIX . '14',
            DemoProductDataFixture::PRODUCT_PREFIX . '10',
            DemoProductDataFixture::PRODUCT_PREFIX . '7',
        ];

        $domainId = 2;
        $this->createTopProducts($topProductReferenceNamesOnDomain2, $domainId);
    }

    /**
     * @param string[] $productReferenceNames
     */
    private function createTopProducts(array $productReferenceNames, int $domainId): void
    {
        $products = [];
        foreach ($productReferenceNames as $productReferenceName) {
            $products[] = $this->getReference($productReferenceName);
        }

        $this->topProductFacade->saveTopProductsForDomain($domainId, $products);
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [
            ProductDataFixture::class,
        ];
    }
}
