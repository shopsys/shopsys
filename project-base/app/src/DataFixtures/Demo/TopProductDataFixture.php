<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Product\Product;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade;

class TopProductDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade $topProductFacade
     */
    public function __construct(
        private readonly TopProductFacade $topProductFacade,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $defaultTopProductReferenceNames = [
            ProductDataFixture::PRODUCT_PREFIX . '1',
            ProductDataFixture::PRODUCT_PREFIX . '2',
            ProductDataFixture::PRODUCT_PREFIX . '69', // main variant
            ProductDataFixture::PRODUCT_PREFIX . '3',
            ProductDataFixture::PRODUCT_PREFIX . '4',
            ProductDataFixture::PRODUCT_PREFIX . '5',
            ProductDataFixture::PRODUCT_PREFIX . '6',
            ProductDataFixture::PRODUCT_PREFIX . '7',
            ProductDataFixture::PRODUCT_PREFIX . '8',
            ProductDataFixture::PRODUCT_PREFIX . '9',
            ProductDataFixture::PRODUCT_PREFIX . '10', // sold out
            ProductDataFixture::PRODUCT_PREFIX . '17',
            ProductDataFixture::PRODUCT_PREFIX . '76', // excluded from sale
            ProductDataFixture::PRODUCT_PREFIX . '148', // variant
        ];
        $distinctTopProductReferenceNames = [
            ProductDataFixture::PRODUCT_PREFIX . '14',
            ProductDataFixture::PRODUCT_PREFIX . '10',
            ProductDataFixture::PRODUCT_PREFIX . '7',
        ];

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomainIds() as $domainId) {
            if ($domainId === Domain::SECOND_DOMAIN_ID) {
                $this->createTopProductsForDomain($distinctTopProductReferenceNames, $domainId);
            } else {
                $this->createTopProductsForDomain($defaultTopProductReferenceNames, $domainId);
            }
        }
    }

    /**
     * @param string[] $productReferenceNames
     * @param int $domainId
     */
    private function createTopProductsForDomain(array $productReferenceNames, int $domainId): void
    {
        $products = [];

        foreach ($productReferenceNames as $productReferenceName) {
            $products[] = $this->getReference($productReferenceName, Product::class);
        }

        $this->topProductFacade->saveTopProductsForDomain($domainId, $products);
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            ProductDataFixture::class,
        ];
    }
}
