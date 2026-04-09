<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\FriendlyUrl;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlCacheKeyProvider;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository;
use Symfony\Contracts\Cache\CacheInterface;

class FriendlyUrlSlugBatchLoader
{
    public function __construct(
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly Domain $domain,
        protected readonly FriendlyUrlRepository $friendlyUrlRepository,
        protected readonly CacheInterface $mainFriendlyUrlSlugCache,
        protected readonly FriendlyUrlCacheKeyProvider $friendlyUrlCacheKeyProvider,
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
        return $this->loadSlugs($entityIds, 'front_flag_detail');
    }

    /**
     * @param int[] $entityIds
     */
    public function loadBlogCategorySlugs(array $entityIds): Promise
    {
        return $this->loadSlugs($entityIds, 'front_blogcategory_detail');
    }

    /**
     * @param int[] $entityIds
     */
    public function loadCategorySlugs(array $entityIds): Promise
    {
        return $this->loadSlugs($entityIds, 'front_product_list');
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
    public function loadProductSlugs(array $entityIds): Promise
    {
        return $this->loadSlugs($entityIds, 'front_product_detail');
    }

    /**
     * @param int[] $entityIds
     */
    protected function loadSlugs(array $entityIds, string $routeName): Promise
    {
        $domainId = $this->domain->getId();
        $slugsByEntityId = [];
        $missingEntityIds = [];

        foreach ($entityIds as $entityId) {
            $cacheKey = $this->friendlyUrlCacheKeyProvider->getMainFriendlyUrlSlugCacheKey($routeName, $domainId, $entityId);
            $cachedSlug = $this->mainFriendlyUrlSlugCache->get($cacheKey, fn () => null);

            if ($cachedSlug !== null) {
                $slugsByEntityId[$entityId] = $cachedSlug;
            } else {
                $missingEntityIds[] = $entityId;
            }
        }

        if ($missingEntityIds !== []) {
            $friendlyUrls = $this->friendlyUrlRepository->getMainFriendlyUrlsByEntitiesIndexedByEntityId(
                $missingEntityIds,
                $routeName,
                $domainId,
            );

            foreach ($missingEntityIds as $entityId) {
                if (!isset($friendlyUrls[$entityId])) {
                    throw new FriendlyUrlNotFoundException(
                        sprintf('Main friendly URL not found for route "%s", domain ID "%d", and entity ID "%d".', $routeName, $domainId, $entityId),
                    );
                }

                $slug = $friendlyUrls[$entityId]->getSlug();
                $slugsByEntityId[$entityId] = $slug;

                $cacheKey = $this->friendlyUrlCacheKeyProvider->getMainFriendlyUrlSlugCacheKey($routeName, $domainId, $entityId);
                $this->mainFriendlyUrlSlugCache->delete($cacheKey);
                $this->mainFriendlyUrlSlugCache->get($cacheKey, fn () => $slug);
            }
        }

        $slugs = [];

        foreach ($entityIds as $entityId) {
            $slugs[] = '/' . $slugsByEntityId[$entityId];
        }

        return $this->promiseAdapter->all($slugs);
    }
}
