<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Category\Category;
use App\Model\Product\Product;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\ManualBestsellingProductFacade;

class BestsellingProductDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\ManualBestsellingProductFacade $manualBestsellingProductFacade
     */
    public function __construct(
        private readonly ManualBestsellingProductFacade $manualBestsellingProductFacade,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomainIds() as $domainId) {
            if ($domainId !== Domain::SECOND_DOMAIN_ID) {
                $productsIndexedByPosition = [
                    0 => $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '7', Product::class),
                    2 => $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '8', Product::class),
                    8 => $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '5', Product::class),
                ];
            } else {
                $productsIndexedByPosition = [$this->getReference(ProductDataFixture::PRODUCT_PREFIX . '7', Product::class)];
            }
            $this->manualBestsellingProductFacade->edit(
                $this->getReference(CategoryDataFixture::CATEGORY_PHOTO, Category::class),
                $domainId,
                $productsIndexedByPosition,
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            ProductDataFixture::class,
            CategoryDataFixture::class,
        ];
    }
}
