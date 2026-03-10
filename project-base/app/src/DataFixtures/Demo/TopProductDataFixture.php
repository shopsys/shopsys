<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Product\Product;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade;

class TopProductDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly TopProductFacade $topProductFacade,
    ) {
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        $topProductReferenceIds = [
            1,
            2,
            69, // main variant
            3,
            4,
            5,
            6,
            7,
            8,
            9,
            10, // sold out
            17,
            76, // excluded from sale
            148, // variant
        ];

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomainIds() as $domainId) {
            $this->createTopProductsForDomain($topProductReferenceIds, $domainId);
        }
    }

    /**
     * @param int[] $productReferenceIds
     */
    private function createTopProductsForDomain(array $productReferenceIds, int $domainId): void
    {
        $products = [];

        foreach ($productReferenceIds as $productReferenceId) {
            $products[] = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productReferenceId, Product::class);
        }

        $this->topProductFacade->saveTopProductsForDomain($domainId, $products);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDependencies(): array
    {
        return [
            ProductDataFixture::class,
        ];
    }
}
