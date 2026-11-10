<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\FriendlyUrl;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade as FrameworkFriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryDetailFriendlyUrlDataProvider;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagDetailFriendlyUrlDataProvider;
use Shopsys\FrameworkBundle\Model\Product\ProductListFriendlyUrlDataProvider;

class FriendlyUrlSlugBatchLoader
{
    public function __construct(
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly Domain $domain,
        protected readonly FrameworkFriendlyUrlFacade $frameworkFriendlyUrlFacade,
    ) {
    }

    /**
     * @param int[] $entityIds
     */
    public function loadBrandSlugs(array $entityIds): Promise
    {
        return $this->loadSlugs($entityIds, 'front_brand_detail');
    }

    /**
     * @param int[] $entityIds
     */
    public function loadStoreSlugs(array $entityIds): Promise
    {
        return $this->loadSlugs($entityIds, 'front_stores_detail');
    }

    /**
     * @param int[] $entityIds
     */
    public function loadFlagSlugs(array $entityIds): Promise
    {
        return $this->loadSlugs($entityIds, FlagDetailFriendlyUrlDataProvider::ROUTE_NAME);
    }

    /**
     * @param int[] $entityIds
     */
    public function loadBlogCategorySlugs(array $entityIds): Promise
    {
        return $this->loadSlugs($entityIds, BlogCategoryDetailFriendlyUrlDataProvider::ROUTE_NAME);
    }

    /**
     * @param int[] $entityIds
     */
    public function loadBlogCategoryMainFriendlyUrlSlugs(array $entityIds): Promise
    {
        return $this->loadMainFriendlyUrlSlugs($entityIds, BlogCategoryDetailFriendlyUrlDataProvider::ROUTE_NAME);
    }

    /**
     * @param int[] $entityIds
     */
    public function loadCategorySlugs(array $entityIds): Promise
    {
        return $this->loadSlugs($entityIds, ProductListFriendlyUrlDataProvider::ROUTE_NAME);
    }

    /**
     * @param int[] $entityIds
     */
    public function loadCategorySeoSlugs(array $entityIds): Promise
    {
        return $this->loadSlugs($entityIds, 'front_category_seo');
    }

    /**
     * @param int[] $entityIds
     */
    protected function loadSlugs(array $entityIds, string $routeName): Promise
    {
        $slugsByEntityId = $this->getSlugsIndexedByEntityId($entityIds, $routeName);
        $slugs = [];

        foreach ($entityIds as $entityId) {
            $slugs[] = $slugsByEntityId[$entityId];
        }

        return $this->promiseAdapter->all($slugs);
    }

    /**
     * @param int[] $entityIds
     */
    protected function loadMainFriendlyUrlSlugs(array $entityIds, string $routeName): Promise
    {
        $mainFriendlyUrlSlugsIndexedByEntityId = $this->getMainFriendlyUrlSlugsIndexedByEntityIdOnDomain(
            $entityIds,
            $routeName,
            $this->domain->getId(),
        );
        $mainFriendlyUrlSlugs = [];

        foreach ($entityIds as $entityId) {
            $mainFriendlyUrlSlugs[] = $mainFriendlyUrlSlugsIndexedByEntityId[$entityId];
        }

        return $this->promiseAdapter->all($mainFriendlyUrlSlugs);
    }

    /**
     * @param int[] $entityIds
     * @return array<int, string>
     */
    public function getSlugsIndexedByEntityId(array $entityIds, string $routeName): array
    {
        $mainFriendlyUrlSlugsIndexedByEntityId = $this->getMainFriendlyUrlSlugsIndexedByEntityIdOnDomain(
            $entityIds,
            $routeName,
            $this->domain->getId(),
        );
        $slugsIndexedByEntityId = [];

        foreach ($mainFriendlyUrlSlugsIndexedByEntityId as $entityId => $mainFriendlyUrlSlug) {
            $slugsIndexedByEntityId[$entityId] = '/' . $mainFriendlyUrlSlug;
        }

        return $slugsIndexedByEntityId;
    }

    /**
     * @param int[] $entityIds
     * @return array<int, string>
     */
    public function getMainFriendlyUrlSlugsIndexedByEntityIdOnDomain(
        array $entityIds,
        string $routeName,
        int $domainId,
    ): array {
        return $this->frameworkFriendlyUrlFacade->getMainFriendlyUrlSlugsIndexedByEntityId($domainId, $routeName, $entityIds);
    }
}
