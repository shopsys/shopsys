<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\AdditionalService;

use App\DataFixtures\Demo\ProductDataFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServiceDataFactory;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServiceFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactory;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Tests\App\Test\TransactionFunctionalTestCase;

class ProductAdditionalServiceAssignmentTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private AdditionalServiceFacade $additionalServiceFacade;

    /**
     * @inject
     */
    private AdditionalServiceDataFactory $additionalServiceDataFactory;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private ProductDataFactory $productDataFactory;

    public function testAdditionalServicesAreAssignedToProductPerDomain(): void
    {
        $additionalService = $this->createAdditionalService();
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 5, Product::class);

        $productData = $this->productDataFactory->createFromProduct($product);
        $productData->additionalServicesByDomainId[Domain::FIRST_DOMAIN_ID] = [$additionalService];
        $this->productFacade->edit($product->getId(), $productData);

        $this->em->refresh($product);

        $this->assertSame(
            [Domain::FIRST_DOMAIN_ID => [$additionalService->getId()]],
            $this->getAdditionalServiceIdsIndexedByDomainId($product),
        );
    }

    public function testAdditionalServicesAreNotAssignableToMainVariant(): void
    {
        $additionalService = $this->createAdditionalService();
        $mainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 69, Product::class);
        $this->assertTrue($mainVariant->isMainVariant());

        $mainVariantData = $this->productDataFactory->createFromProduct($mainVariant);
        $mainVariantData->additionalServicesByDomainId[Domain::FIRST_DOMAIN_ID] = [$additionalService];
        $this->productFacade->edit($mainVariant->getId(), $mainVariantData);

        $this->em->refresh($mainVariant);

        $this->assertSame([], $this->getAdditionalServiceIdsIndexedByDomainId($mainVariant));
    }

    public function testAdditionalServicesAreAssignableToVariant(): void
    {
        $additionalService = $this->createAdditionalService();
        $variant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 74, Product::class);
        $this->assertTrue($variant->isVariant());

        $variantData = $this->productDataFactory->createFromProduct($variant);
        $variantData->additionalServicesByDomainId[Domain::FIRST_DOMAIN_ID] = [$additionalService];
        $this->productFacade->edit($variant->getId(), $variantData);

        $this->em->refresh($variant);

        $this->assertSame(
            [Domain::FIRST_DOMAIN_ID => [$additionalService->getId()]],
            $this->getAdditionalServiceIdsIndexedByDomainId($variant),
        );
    }

    /**
     * @return array<int, int[]>
     */
    private function getAdditionalServiceIdsIndexedByDomainId(Product $product): array
    {
        $additionalServiceIdsByDomainId = [];

        foreach ($product->getAdditionalServicesIndexedByDomainId() as $domainId => $additionalServices) {
            $additionalServiceIdsByDomainId[$domainId] = array_map(
                static fn (AdditionalService $additionalService) => $additionalService->getId(),
                $additionalServices,
            );
        }

        return $additionalServiceIdsByDomainId;
    }

    private function createAdditionalService(): AdditionalService
    {
        $additionalServiceData = $this->additionalServiceDataFactory->create();
        $additionalServiceData->catnum = 'SERVICE-ASSIGN';

        foreach (array_keys($additionalServiceData->name) as $locale) {
            $additionalServiceData->name[$locale] = 'Assignable service';
        }

        foreach (array_keys($additionalServiceData->enabledByDomainId) as $domainId) {
            $additionalServiceData->pricesIndexedByDomainId[$domainId] = Money::create(100);
        }

        return $this->additionalServiceFacade->create($additionalServiceData);
    }
}
