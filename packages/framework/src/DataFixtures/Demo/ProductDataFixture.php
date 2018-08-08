<?php

namespace Shopsys\FrameworkBundle\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade;
use Shopsys\FrameworkBundle\DataFixtures\ProductDataFixtureReferenceInjector;
use Shopsys\FrameworkBundle\Model\Product\ProductData;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductVariantFacade;

class ProductDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    const PRODUCT_PREFIX = 'product_';

    /** @var \Shopsys\FrameworkBundle\DataFixtures\Demo\ProductDataFixtureLoader */
    private $productDataFixtureLoader;

    /** @var \Shopsys\FrameworkBundle\DataFixtures\ProductDataFixtureReferenceInjector */
    private $referenceInjector;

    /** @var \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade */
    private $persistentReferenceFacade;

    /** @var \Shopsys\FrameworkBundle\DataFixtures\Demo\ProductDataFixtureCsvReader */
    private $productDataFixtureCsvReader;

    /** @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade */
    private $productFacade;

    /** @var \Shopsys\FrameworkBundle\Model\Product\ProductVariantFacade */
    private $productVariantFacade;

    public function __construct(
        ProductDataFixtureLoader $productDataFixtureLoader,
        ProductDataFixtureReferenceInjector $referenceInjector,
        PersistentReferenceFacade $persistentReferenceFacade,
        ProductDataFixtureCsvReader $productDataFixtureCsvReader,
        ProductFacade $productFacade,
        ProductVariantFacade $productVariantFacade
    ) {
        $this->productDataFixtureLoader = $productDataFixtureLoader;
        $this->referenceInjector = $referenceInjector;
        $this->persistentReferenceFacade = $persistentReferenceFacade;
        $this->productDataFixtureCsvReader = $productDataFixtureCsvReader;
        $this->productFacade = $productFacade;
        $this->productVariantFacade = $productVariantFacade;
    }

    public function load(ObjectManager $manager): void
    {
        $onlyForFirstDomain = true;
        $this->referenceInjector->loadReferences($this->productDataFixtureLoader, $this->persistentReferenceFacade, $onlyForFirstDomain);

        $csvRows = $this->productDataFixtureCsvReader->getProductDataFixtureCsvRows();
        $productNo = 1;
        $productsByCatnum = [];
        foreach ($csvRows as $row) {
            $productData = $this->productDataFixtureLoader->createProductDataFromRowForFirstDomain($row);
            $product = $this->createProduct(self::PRODUCT_PREFIX . $productNo, $productData);

            if ($product->getCatnum() !== null) {
                $productsByCatnum[$product->getCatnum()] = $product;
            }
            $productNo++;
        }

        $this->createVariants($productsByCatnum, $productNo);
    }
    
    private function createProduct(string $referenceName, ProductData $productData): \Shopsys\FrameworkBundle\Model\Product\Product
    {
        $product = $this->productFacade->create($productData);

        $this->addReference($referenceName, $product);

        return $product;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $productsByCatnum
     */
    private function createVariants(array $productsByCatnum, int $productNo): void
    {
        $csvRows = $this->productDataFixtureCsvReader->getProductDataFixtureCsvRows();
        $variantCatnumsByMainVariantCatnum = $this->productDataFixtureLoader->getVariantCatnumsIndexedByMainVariantCatnum($csvRows);

        foreach ($variantCatnumsByMainVariantCatnum as $mainVariantCatnum => $variantsCatnums) {
            $mainProduct = $productsByCatnum[$mainVariantCatnum];
            /* @var $mainProduct \Shopsys\FrameworkBundle\Model\Product\Product */

            $variants = [];
            foreach ($variantsCatnums as $variantCatnum) {
                $variants[] = $productsByCatnum[$variantCatnum];
            }

            $mainVariant = $this->productVariantFacade->createVariant($mainProduct, $variants);
            $this->addReference(self::PRODUCT_PREFIX . $productNo, $mainVariant);
            $productNo++;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return ProductDataFixtureReferenceInjector::getDependenciesForFirstDomain();
    }
}
