<?php

namespace Shopsys\FrameworkBundle\DataFixtures\DemoMultidomain;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade;
use Shopsys\FrameworkBundle\DataFixtures\Demo\ProductDataFixtureCsvReader;
use Shopsys\FrameworkBundle\DataFixtures\Demo\ProductDataFixtureLoader;
use Shopsys\FrameworkBundle\DataFixtures\ProductDataFixtureReferenceInjector;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;

class ProductDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
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

    /** @var \Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface */
    private $productDataFactory;

    public function __construct(
        ProductDataFixtureLoader $productDataFixtureLoader,
        ProductDataFixtureReferenceInjector $referenceInjector,
        PersistentReferenceFacade $persistentReferenceFacade,
        ProductDataFixtureCsvReader $productDataFixtureCsvReader,
        ProductFacade $productFacade,
        ProductDataFactoryInterface $productDataFactory
    ) {
        $this->productDataFixtureLoader = $productDataFixtureLoader;
        $this->referenceInjector = $referenceInjector;
        $this->persistentReferenceFacade = $persistentReferenceFacade;
        $this->productDataFixtureCsvReader = $productDataFixtureCsvReader;
        $this->productFacade = $productFacade;
        $this->productDataFactory = $productDataFactory;
    }

    public function load(ObjectManager $manager)
    {
        $onlyForFirstDomain = false;
        $this->referenceInjector->loadReferences($this->productDataFixtureLoader, $this->persistentReferenceFacade, $onlyForFirstDomain);

        $csvRows = $this->productDataFixtureCsvReader->getProductDataFixtureCsvRows();
        foreach ($csvRows as $row) {
            $productCatnum = $this->productDataFixtureLoader->getCatnumFromRow($row);
            $product = $this->productFacade->getOneByCatnumExcludeMainVariants($productCatnum);
            $this->editProduct($product, $row);

            if ($product->isVariant() && $product->getCatnum() === $product->getMainVariant()->getCatnum()) {
                $this->editProduct($product->getMainVariant(), $row);
            }
        }
    }

    private function editProduct(Product $product, array $row)
    {
        $productData = $this->productDataFactory->createFromProduct($product);
        $this->productDataFixtureLoader->updateProductDataFromCsvRowForSecondDomain($productData, $row);
        $this->productFacade->edit($product->getId(), $productData);
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return ProductDataFixtureReferenceInjector::getDependenciesForMultidomain();
    }
}
