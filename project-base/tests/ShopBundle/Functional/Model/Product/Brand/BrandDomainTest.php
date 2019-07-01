<?php

namespace Tests\ShopBundle\Functional\Model\Product\Brand;

use Shopsys\FrameworkBundle\Model\Product\Brand\BrandDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFactoryInterface;
use Shopsys\ShopBundle\Model\Product\Brand\Brand;
use Tests\ShopBundle\Test\TransactionFunctionalTestCase;

class BrandDomainTest extends TransactionFunctionalTestCase
{
    protected const FIRST_DOMAIN_ID = 1;
    protected const SECOND_DOMAIN_ID = 2;
    protected const DEMONSTRATIVE_SEO_TITLE = 'Demonstrative seo title';
    protected const DEMONSTRATIVE_SEO_H1 = 'Demonstrative seo h1';

    /**
     * @var \Shopsys\ShopBundle\Model\Product\Brand\BrandDataFactory
     */
    private $brandDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFactory
     */
    private $brandFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator
     */
    private $em;

    public function setUp()
    {
        parent::setUp();
        $this->brandDataFactory = $this->getContainer()->get(BrandDataFactoryInterface::class);
        $this->brandFactory = $this->getContainer()->get(BrandFactoryInterface::class);
        $this->em = $this->getEntityManager();
    }

    /**
     * @group multidomain
     */
    public function testCreateBrandDomain()
    {
        $brandData = $this->brandDataFactory->create();

        $brandData->seoTitles[self::FIRST_DOMAIN_ID] = self::DEMONSTRATIVE_SEO_TITLE;
        $brandData->seoH1s[self::SECOND_DOMAIN_ID] = self::DEMONSTRATIVE_SEO_H1;

        $brand = $this->brandFactory->create($brandData);

        $refreshedBrand = $this->getRefreshedBrandFromDatabase($brand);

        $this->assertSame(self::DEMONSTRATIVE_SEO_TITLE, $refreshedBrand->getSeoTitle(self::FIRST_DOMAIN_ID));
        $this->assertNull($refreshedBrand->getSeoTitle(self::SECOND_DOMAIN_ID));
        $this->assertSame(self::DEMONSTRATIVE_SEO_H1, $refreshedBrand->getSeoH1(self::SECOND_DOMAIN_ID));
        $this->assertNull($refreshedBrand->getSeoH1(self::FIRST_DOMAIN_ID));
    }

    /**
     * @group singledomain
     */
    public function testCreateBrandDomainForSingleDomain()
    {
        $brandData = $this->brandDataFactory->create();

        $brandData->seoTitles[self::FIRST_DOMAIN_ID] = self::DEMONSTRATIVE_SEO_TITLE;
        $brandData->seoH1s[self::FIRST_DOMAIN_ID] = self::DEMONSTRATIVE_SEO_H1;

        $brand = $this->brandFactory->create($brandData);

        $refreshedBrand = $this->getRefreshedBrandFromDatabase($brand);

        $this->assertSame(self::DEMONSTRATIVE_SEO_TITLE, $refreshedBrand->getSeoTitle(self::FIRST_DOMAIN_ID));
        $this->assertSame(self::DEMONSTRATIVE_SEO_H1, $refreshedBrand->getSeoH1(self::FIRST_DOMAIN_ID));
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Product\Brand\Brand $brand
     * @return \Shopsys\ShopBundle\Model\Product\Brand\Brand
     */
    private function getRefreshedBrandFromDatabase(Brand $brand)
    {
        $this->em->persist($brand);
        $this->em->flush();

        $brandId = $brand->getId();

        $this->em->clear();

        return $this->em->getRepository(Brand::class)->find($brandId);
    }
}
