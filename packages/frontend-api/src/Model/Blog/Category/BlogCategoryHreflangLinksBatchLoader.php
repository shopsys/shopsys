<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Blog\Category;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryDetailFriendlyUrlDataProvider;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLink;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade;
use Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlSlugBatchLoader;

class BlogCategoryHreflangLinksBatchLoader
{
    public function __construct(
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly BlogCategoryFacade $blogCategoryFacade,
        protected readonly HreflangLinksFacade $hreflangLinksFacade,
        protected readonly FriendlyUrlSlugBatchLoader $friendlyUrlSlugBatchLoader,
        protected readonly Domain $domain,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[] $blogCategories
     */
    public function loadByBlogCategories(array $blogCategories): Promise
    {
        $relevantDomainIds = $this->hreflangLinksFacade->getRelevantDomainIds($this->domain->getId());
        $visibleDomainIdsIndexedByBlogCategoryId = $this->blogCategoryFacade->getVisibleDomainIdsByBlogCategoriesAndDomainIdsIndexedByBlogCategoryId(
            $blogCategories,
            $relevantDomainIds,
        );
        $mainFriendlyUrlSlugsIndexedByDomainIdAndBlogCategoryId = $this->getMainFriendlyUrlSlugsIndexedByDomainIdAndBlogCategoryId(
            $relevantDomainIds,
            $visibleDomainIdsIndexedByBlogCategoryId,
        );
        $hreflangLinksByBlogCategory = [];

        foreach ($blogCategories as $blogCategory) {
            $hreflangLinks = [];
            $visibleDomainIds = $visibleDomainIdsIndexedByBlogCategoryId[$blogCategory->getId()] ?? [];

            foreach ($relevantDomainIds as $domainId) {
                if (!in_array($domainId, $visibleDomainIds, true)) {
                    continue;
                }

                $hreflangLinks[] = $this->createHreflangLink(
                    $domainId,
                    $mainFriendlyUrlSlugsIndexedByDomainIdAndBlogCategoryId[$domainId][$blogCategory->getId()],
                );
            }

            $hreflangLinksByBlogCategory[] = $hreflangLinks;
        }

        return $this->promiseAdapter->all($hreflangLinksByBlogCategory);
    }

    /**
     * @param int[] $relevantDomainIds
     * @param array<int, int[]> $visibleDomainIdsIndexedByBlogCategoryId
     * @return array<int, array<int, string>>
     */
    protected function getMainFriendlyUrlSlugsIndexedByDomainIdAndBlogCategoryId(
        array $relevantDomainIds,
        array $visibleDomainIdsIndexedByBlogCategoryId,
    ): array {
        $mainFriendlyUrlSlugsIndexedByDomainIdAndBlogCategoryId = [];

        foreach ($relevantDomainIds as $domainId) {
            $blogCategoryIdsVisibleOnDomain = [];

            foreach ($visibleDomainIdsIndexedByBlogCategoryId as $blogCategoryId => $visibleDomainIds) {
                if (in_array($domainId, $visibleDomainIds, true)) {
                    $blogCategoryIdsVisibleOnDomain[] = $blogCategoryId;
                }
            }

            if ($blogCategoryIdsVisibleOnDomain === []) {
                continue;
            }

            $mainFriendlyUrlSlugsIndexedByDomainIdAndBlogCategoryId[$domainId] = $this->friendlyUrlSlugBatchLoader->getMainFriendlyUrlSlugsIndexedByEntityIdOnDomain(
                $blogCategoryIdsVisibleOnDomain,
                BlogCategoryDetailFriendlyUrlDataProvider::ROUTE_NAME,
                $domainId,
            );
        }

        return $mainFriendlyUrlSlugsIndexedByDomainIdAndBlogCategoryId;
    }

    protected function createHreflangLink(int $domainId, string $mainFriendlyUrlSlug): HreflangLink
    {
        return new HreflangLink(
            $this->domain->getDomainConfigById($domainId)->getLocale(),
            $this->friendlyUrlFacade->getAbsoluteUrlByMainFriendlyUrlSlug($domainId, $mainFriendlyUrlSlug),
            $domainId,
        );
    }
}
