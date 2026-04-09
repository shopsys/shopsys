<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Category;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlCacheKeyProvider;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver;
use Symfony\Contracts\Cache\CacheInterface;

class ReadyCategorySeoMixBatchLoader
{
    protected const string ROUTE_NAME = 'front_category_seo';

    public function __construct(
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly ReadyCategorySeoMixFacade $readyCategorySeoMixFacade,
        protected readonly Domain $domain,
        protected readonly FriendlyUrlRepository $friendlyUrlRepository,
        protected readonly CustomerUserRoleResolver $customerUserRoleResolver,
        protected readonly CacheInterface $mainFriendlyUrlSlugCache,
        protected readonly FriendlyUrlCacheKeyProvider $friendlyUrlCacheKeyProvider,
    ) {
    }

    /**
     * @param int[] $categoryIds
     */
    public function loadByCategoryIds(array $categoryIds): Promise
    {
        $allReadyCategorySeoMixes = $this->readyCategorySeoMixFacade->getAllIndexedByCategoryId($categoryIds, $this->domain->getCurrentDomainConfig());

        $canSeePrices = $this->customerUserRoleResolver->canCurrentCustomerUserSeePrices();

        $allSeoMixIds = [];

        foreach ($allReadyCategorySeoMixes as $readyCategorySeoMixes) {
            foreach ($readyCategorySeoMixes as $readyCategorySeoMix) {
                $allSeoMixIds[] = $readyCategorySeoMix->getId();
            }
        }

        $slugsByEntityId = $this->loadSlugsWithCache($allSeoMixIds);

        $result = [];

        foreach ($allReadyCategorySeoMixes as $readyCategorySeoMixes) {
            $filteredMixes = $canSeePrices
                ? $readyCategorySeoMixes
                : array_filter($readyCategorySeoMixes, fn (ReadyCategorySeoMix $readyCategorySeoMix) => !$readyCategorySeoMix->hasPriceBasedOrdering());

            $result[] = array_map(
                function (ReadyCategorySeoMix $readyCategorySeoMix) use ($slugsByEntityId) {
                    if (!isset($slugsByEntityId[$readyCategorySeoMix->getId()])) {
                        throw new FriendlyUrlNotFoundException(
                            sprintf('Main friendly URL not found for route "front_category_seo", domain ID "%d", and entity ID "%d".', $this->domain->getId(), $readyCategorySeoMix->getId()),
                        );
                    }

                    return [
                        'name' => $readyCategorySeoMix->getH1(),
                        'slug' => '/' . $slugsByEntityId[$readyCategorySeoMix->getId()],
                    ];
                },
                $filteredMixes,
            );
        }

        return $this->promiseAdapter->all($result);
    }

    /**
     * @param int[] $entityIds
     * @return array<int, string>
     */
    protected function loadSlugsWithCache(array $entityIds): array
    {
        $domainId = $this->domain->getId();
        $slugsByEntityId = [];
        $missingEntityIds = [];

        foreach ($entityIds as $entityId) {
            $cacheKey = $this->friendlyUrlCacheKeyProvider->getMainFriendlyUrlSlugCacheKey(static::ROUTE_NAME, $domainId, $entityId);
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
                static::ROUTE_NAME,
                $domainId,
            );

            foreach ($missingEntityIds as $entityId) {
                if (!isset($friendlyUrls[$entityId])) {
                    throw new FriendlyUrlNotFoundException(
                        sprintf('Main friendly URL not found for route "%s", domain ID "%d", and entity ID "%d".', static::ROUTE_NAME, $domainId, $entityId),
                    );
                }

                $slug = $friendlyUrls[$entityId]->getSlug();
                $slugsByEntityId[$entityId] = $slug;

                $cacheKey = $this->friendlyUrlCacheKeyProvider->getMainFriendlyUrlSlugCacheKey(static::ROUTE_NAME, $domainId, $entityId);
                $this->mainFriendlyUrlSlugCache->delete($cacheKey);
                $this->mainFriendlyUrlSlugCache->get($cacheKey, fn () => $slug);
            }
        }

        return $slugsByEntityId;
    }
}
