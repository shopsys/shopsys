<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Availability;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Exception\DeprecatedAvailabilityPropertyFromProductException;
use Shopsys\FrameworkBundle\Model\Product\Availability\Availability;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

final class AvailabilityFacadeTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityDataFactoryInterface
     * @inject
     */
    private $availabilityDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade
     * @inject
     */
    private $availabilityFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface
     * @inject
     */
    private $productDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     * @inject
     */
    private $productFacade;

    public function testDeleteByIdAndReplaceProductAvailability(): void
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');
        $productData = $this->productDataFactory->createFromProduct($product);

        $availabilityToDelete = $this->createNewAvailability();
        $productData->usingStock = false;
        $productData->availability = $availabilityToDelete;

        $this->productFacade->edit($product->getId(), $productData);

        $availabilityToReplaceWith = $this->createNewAvailability();
        $this->availabilityFacade->deleteById($availabilityToDelete->getId(), $availabilityToReplaceWith->getId());

        $this->em->refresh($product);

        try {
            $this->assertSame($availabilityToReplaceWith, $product->getAvailability());
        } catch (DeprecatedAvailabilityPropertyFromProductException $exception) {
            $this->assertSame($availabilityToReplaceWith, $exception->getAvailability());
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
     */
    private function createNewAvailability(): Availability
    {
        $availabilityData = $this->availabilityDataFactory->create();

        foreach (array_keys($availabilityData->name) as $locale) {
            $availabilityData->name[$locale] = 'new availability';
        }

        return $this->availabilityFacade->create($availabilityData);
    }
}
