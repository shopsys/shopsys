<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use App\Model\Product\Product;
use App\Model\Product\ProductData;
use App\Model\Product\ProductDataFactory;
use PHPUnit\Framework\Attributes\Group;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\ProductInputPriceDataFactory;
use Tests\App\Test\TransactionFunctionalTestCase;

class ProductDomainTest extends TransactionFunctionalTestCase
{
    protected const int FIRST_DOMAIN_ID = 1;
    protected const int SECOND_DOMAIN_ID = 2;
    protected const string DEMONSTRATIVE_DESCRIPTION = 'Demonstrative description';
    protected const string DEMONSTRATIVE_SEO_TITLE = 'Demonstrative seo title';
    protected const string DEMONSTRATIVE_SEO_META_DESCRIPTION = 'Demonstrative seo description';
    protected const string DEMONSTRATIVE_SEO_H1 = 'Demonstrative seo H1';
    protected const string DEMONSTRATIVE_SHORT_DESCRIPTION = 'Demonstrative short description';

    /**
     * @inject
     */
    private ProductDataFactory $productDataFactory;

    /**
     * @inject
     */
    private ProductFactoryInterface $productFactory;

    /**
     * @inject
     */
    private VatFacade $vatFacade;

    /**
     * @inject
     */
    private ProductInputPriceDataFactory $productInputPriceDataFactory;

    #[Group('multidomain')]
    public function testCreateProductDomainWithData(): void
    {
        $productData = $this->productDataFactory->create();

        $productData->seoTitles[self::FIRST_DOMAIN_ID] = self::DEMONSTRATIVE_SEO_TITLE;
        $productData->seoMetaDescriptions[self::SECOND_DOMAIN_ID] = self::DEMONSTRATIVE_SEO_META_DESCRIPTION;
        $productData->seoH1s[self::FIRST_DOMAIN_ID] = self::DEMONSTRATIVE_SEO_H1;
        $productData->descriptions[self::SECOND_DOMAIN_ID] = self::DEMONSTRATIVE_DESCRIPTION;
        $productData->shortDescriptions[self::FIRST_DOMAIN_ID] = self::DEMONSTRATIVE_SHORT_DESCRIPTION;
        $productData->catnum = '123';

        $this->setVatsAndPrices($productData);

        /** @var \App\Model\Product\Product $product */
        $product = $this->productFactory->create($productData);

        $refreshedProduct = $this->getRefreshedProductFromDatabase($product);

        $this->assertSame(self::DEMONSTRATIVE_SEO_TITLE, $refreshedProduct->getSeoTitle(self::FIRST_DOMAIN_ID));
        $this->assertNull($refreshedProduct->getSeoTitle(self::SECOND_DOMAIN_ID));
        $this->assertSame(
            self::DEMONSTRATIVE_SEO_META_DESCRIPTION,
            $refreshedProduct->getSeoMetaDescription(self::SECOND_DOMAIN_ID),
        );
        $this->assertNull($refreshedProduct->getSeoMetaDescription(self::FIRST_DOMAIN_ID));
        $this->assertSame(self::DEMONSTRATIVE_SEO_H1, $refreshedProduct->getSeoH1(self::FIRST_DOMAIN_ID));
        $this->assertNull($refreshedProduct->getSeoH1(self::SECOND_DOMAIN_ID));
        $this->assertSame(self::DEMONSTRATIVE_DESCRIPTION, $refreshedProduct->getDescription(self::SECOND_DOMAIN_ID));
        $this->assertNull($refreshedProduct->getDescription(self::FIRST_DOMAIN_ID));
        $this->assertSame(
            self::DEMONSTRATIVE_SHORT_DESCRIPTION,
            $refreshedProduct->getShortDescription(self::FIRST_DOMAIN_ID),
        );
        $this->assertNull($refreshedProduct->getShortDescription(self::SECOND_DOMAIN_ID));
    }

    #[Group('singledomain')]
    public function testCreateProductDomainWithDataForSingleDomain(): void
    {
        $productData = $this->productDataFactory->create();

        $productData->seoTitles[self::FIRST_DOMAIN_ID] = self::DEMONSTRATIVE_SEO_TITLE;
        $productData->seoMetaDescriptions[self::FIRST_DOMAIN_ID] = self::DEMONSTRATIVE_SEO_META_DESCRIPTION;
        $productData->seoH1s[self::FIRST_DOMAIN_ID] = self::DEMONSTRATIVE_SEO_H1;
        $productData->descriptions[self::FIRST_DOMAIN_ID] = self::DEMONSTRATIVE_DESCRIPTION;
        $productData->shortDescriptions[self::FIRST_DOMAIN_ID] = self::DEMONSTRATIVE_SHORT_DESCRIPTION;
        $productData->catnum = '123';
        $this->setVatsAndPrices($productData);

        /** @var \App\Model\Product\Product $product */
        $product = $this->productFactory->create($productData);

        $refreshedProduct = $this->getRefreshedProductFromDatabase($product);

        $this->assertSame(self::DEMONSTRATIVE_SEO_TITLE, $refreshedProduct->getSeoTitle(self::FIRST_DOMAIN_ID));
        $this->assertSame(
            self::DEMONSTRATIVE_SEO_META_DESCRIPTION,
            $refreshedProduct->getSeoMetaDescription(self::FIRST_DOMAIN_ID),
        );
        $this->assertSame(self::DEMONSTRATIVE_SEO_H1, $refreshedProduct->getSeoH1(self::FIRST_DOMAIN_ID));
        $this->assertSame(self::DEMONSTRATIVE_DESCRIPTION, $refreshedProduct->getDescription(self::FIRST_DOMAIN_ID));
        $this->assertSame(
            self::DEMONSTRATIVE_SHORT_DESCRIPTION,
            $refreshedProduct->getShortDescription(self::FIRST_DOMAIN_ID),
        );
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Product\Product
     */
    private function getRefreshedProductFromDatabase(Product $product): Product
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
    private function setVatsAndPrices(ProductData $productData): void
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $productData->productInputPricesByDomain[$domainId] = $this->productInputPriceDataFactory->create(
                $this->vatFacade->getDefaultVatForDomain($domainId),
                [
                    1 => Money::zero(),
                    2 => Money::zero(),
                ],
            );
        }
    }
}
