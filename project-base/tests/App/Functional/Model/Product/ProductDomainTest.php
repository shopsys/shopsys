<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use App\DataFixtures\Demo\AvailabilityDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductData;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class ProductDomainTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    protected const FIRST_DOMAIN_ID = 1;
    protected const SECOND_DOMAIN_ID = 2;
    protected const DEMONSTRATIVE_DESCRIPTION = 'Demonstrative description';
    protected const DEMONSTRATIVE_SEO_TITLE = 'Demonstrative seo title';
    protected const DEMONSTRATIVE_SEO_META_DESCRIPTION = 'Demonstrative seo description';
    protected const DEMONSTRATIVE_SEO_H1 = 'Demonstrative seo H1';
    protected const DEMONSTRATIVE_SHORT_DESCRIPTION = 'Demonstrative short description';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface
     * @inject
     */
    private $productDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFactoryInterface
     * @inject
     */
    private $productFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade
     * @inject
     */
    private $vatFacade;

    /**
     * @group multidomain
     */
    public function testCreateProductDomainWithData()
    {
        $productData = $this->productDataFactory->create();

        $productData->seoTitles[self::FIRST_DOMAIN_ID] = self::DEMONSTRATIVE_SEO_TITLE;
        $productData->seoMetaDescriptions[self::SECOND_DOMAIN_ID] = self::DEMONSTRATIVE_SEO_META_DESCRIPTION;
        $productData->seoH1s[self::FIRST_DOMAIN_ID] = self::DEMONSTRATIVE_SEO_H1;
        $productData->descriptions[self::SECOND_DOMAIN_ID] = self::DEMONSTRATIVE_DESCRIPTION;
        $productData->shortDescriptions[self::FIRST_DOMAIN_ID] = self::DEMONSTRATIVE_SHORT_DESCRIPTION;
        $productData->availability = $this->getReference(AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $productData->outOfStockAvailability = $this->getReference(AvailabilityDataFixture::AVAILABILITY_OUT_OF_STOCK);
        $this->setVats($productData);

        $product = $this->productFactory->create($productData);

        $refreshedProduct = $this->getRefreshedProductFromDatabase($product);

        $this->assertSame(self::DEMONSTRATIVE_SEO_TITLE, $refreshedProduct->getSeoTitle(self::FIRST_DOMAIN_ID));
        $this->assertNull($refreshedProduct->getSeoTitle(self::SECOND_DOMAIN_ID));
        $this->assertSame(self::DEMONSTRATIVE_SEO_META_DESCRIPTION, $refreshedProduct->getSeoMetaDescription(self::SECOND_DOMAIN_ID));
        $this->assertNull($refreshedProduct->getSeoMetaDescription(self::FIRST_DOMAIN_ID));
        $this->assertSame(self::DEMONSTRATIVE_SEO_H1, $refreshedProduct->getSeoH1(self::FIRST_DOMAIN_ID));
        $this->assertNull($refreshedProduct->getSeoH1(self::SECOND_DOMAIN_ID));
        $this->assertSame(self::DEMONSTRATIVE_DESCRIPTION, $refreshedProduct->getDescription(self::SECOND_DOMAIN_ID));
        $this->assertNull($refreshedProduct->getDescription(self::FIRST_DOMAIN_ID));
        $this->assertSame(self::DEMONSTRATIVE_SHORT_DESCRIPTION, $refreshedProduct->getShortDescription(self::FIRST_DOMAIN_ID));
        $this->assertNull($refreshedProduct->getShortDescription(self::SECOND_DOMAIN_ID));
    }

    /**
     * @group singledomain
     */
    public function testCreateProductDomainWithDataForSingleDomain()
    {
        $productData = $this->productDataFactory->create();

        $productData->seoTitles[self::FIRST_DOMAIN_ID] = self::DEMONSTRATIVE_SEO_TITLE;
        $productData->seoMetaDescriptions[self::FIRST_DOMAIN_ID] = self::DEMONSTRATIVE_SEO_META_DESCRIPTION;
        $productData->seoH1s[self::FIRST_DOMAIN_ID] = self::DEMONSTRATIVE_SEO_H1;
        $productData->descriptions[self::FIRST_DOMAIN_ID] = self::DEMONSTRATIVE_DESCRIPTION;
        $productData->shortDescriptions[self::FIRST_DOMAIN_ID] = self::DEMONSTRATIVE_SHORT_DESCRIPTION;
        $productData->availability = $this->getReference(AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $productData->outOfStockAvailability = $this->getReference(AvailabilityDataFixture::AVAILABILITY_OUT_OF_STOCK);
        $this->setVats($productData);

        $product = $this->productFactory->create($productData);

        $refreshedProduct = $this->getRefreshedProductFromDatabase($product);

        $this->assertSame(self::DEMONSTRATIVE_SEO_TITLE, $refreshedProduct->getSeoTitle(self::FIRST_DOMAIN_ID));
        $this->assertSame(self::DEMONSTRATIVE_SEO_META_DESCRIPTION, $refreshedProduct->getSeoMetaDescription(self::FIRST_DOMAIN_ID));
        $this->assertSame(self::DEMONSTRATIVE_SEO_H1, $refreshedProduct->getSeoH1(self::FIRST_DOMAIN_ID));
        $this->assertSame(self::DEMONSTRATIVE_DESCRIPTION, $refreshedProduct->getDescription(self::FIRST_DOMAIN_ID));
        $this->assertSame(self::DEMONSTRATIVE_SHORT_DESCRIPTION, $refreshedProduct->getShortDescription(self::FIRST_DOMAIN_ID));
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Product\Product
     */
    private function getRefreshedProductFromDatabase(Product $product)
    {
        $this->em->persist($product);
        $this->em->flush();

        $productId = $product->getId();

        $this->em->clear();

        return $this->em->getRepository(Product::class)->find($productId);
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    private function setVats(ProductData $productData): void
    {
        $productVatsIndexedByDomainId = [];
        foreach ($this->domain->getAllIds() as $domainId) {
            $productVatsIndexedByDomainId[$domainId] = $this->vatFacade->getDefaultVatForDomain($domainId);
        }
        $productData->vatsIndexedByDomainId = $productVatsIndexedByDomainId;
    }
}
