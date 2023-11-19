<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\ManualBestsellingProductFacade;

class BestsellingProductDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\ManualBestsellingProductFacade $manualBestsellingProductFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly ManualBestsellingProductFacade $manualBestsellingProductFacade,
        private readonly Domain $domain,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $domainId = $domainConfig->getId();

            if ($domainId !== Domain::SECOND_DOMAIN_ID) {
                $productsIndexedByPosition = [
                    0 => $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '7'),
                    2 => $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '8'),
                    8 => $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '5'),
                ];
            } else {
                $productsIndexedByPosition = [$this->getReference(ProductDataFixture::PRODUCT_PREFIX . '7')];
            }
            $this->manualBestsellingProductFacade->edit(
                $this->getReference(CategoryDataFixture::CATEGORY_PHOTO),
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
