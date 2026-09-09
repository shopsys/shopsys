<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Sitemap;

use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Category\Category;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Sitemap\SitemapFacade;
use Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem;
use Tests\App\Test\TransactionFunctionalTestCase;

class SitemapTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private SitemapFacade $sitemapFacade;

    /**
     * @inject
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    public function testProductWithCanonicalUrlIsNotInSitemap(): void
    {
        $productWithCanonicalUrl = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 58, Product::class);
        $productWithoutSeoRestrictions = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);

        $slugs = $this->getSlugs($this->sitemapFacade->getSitemapItemsForListableProducts($this->domain->getCurrentDomainConfig()));

        $this->assertContains($this->getMainSlug('front_product_detail', $productWithoutSeoRestrictions->getId()), $slugs);
        $this->assertNotContains($this->getMainSlug('front_product_detail', $productWithCanonicalUrl->getId()), $slugs);
    }

    public function testCategoryWithNoindexRobotsIsNotInSitemap(): void
    {
        $noindexCategory = $this->getReference(CategoryDataFixture::CATEGORY_PRINTER_SUPPLIES, Category::class);
        $indexedCategory = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class);

        $slugs = $this->getSlugs($this->sitemapFacade->getSitemapItemsForVisibleCategories($this->domain->getCurrentDomainConfig()));

        $this->assertContains($this->getMainSlug('front_product_list', $indexedCategory->getId()), $slugs);
        $this->assertNotContains($this->getMainSlug('front_product_list', $noindexCategory->getId()), $slugs);
    }

    private function getMainSlug(string $routeName, int $entityId): string
    {
        return $this->friendlyUrlFacade->getMainFriendlyUrlSlug($this->domain->getId(), $routeName, $entityId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[] $sitemapItems
     * @return string[]
     */
    private function getSlugs(array $sitemapItems): array
    {
        return array_map(static fn (SitemapItem $sitemapItem): string => $sitemapItem->slug, $sitemapItems);
    }
}
