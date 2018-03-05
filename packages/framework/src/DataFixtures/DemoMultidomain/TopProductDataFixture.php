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
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
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
     * @param int $domainId
     */
    private function createTopProducts(array $productReferenceNames, $domainId)
    {
        $topProductFacade = $this->get(TopProductFacade::class);
        /* @var $topProductFacade \Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade */

        $products = [];
        foreach ($productReferenceNames as $productReferenceName) {
            $products[] = $this->getReference($productReferenceName);
        }

        $topProductFacade->saveTopProductsForDomain($domainId, $products);
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
