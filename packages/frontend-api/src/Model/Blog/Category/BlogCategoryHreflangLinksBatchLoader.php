<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Blog\Category;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
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
        $slugsIndexedByDomainIdAndBlogCategoryId = $this->getSlugsIndexedByDomainIdAndBlogCategoryId(
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
                    $slugsIndexedByDomainIdAndBlogCategoryId[$domainId][$blogCategory->getId()],
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
    protected function getSlugsIndexedByDomainIdAndBlogCategoryId(
        array $relevantDomainIds,
        array $visibleDomainIdsIndexedByBlogCategoryId,
    ): array {
        $slugsIndexedByDomainIdAndBlogCategoryId = [];

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

            $slugsIndexedByDomainIdAndBlogCategoryId[$domainId] = $this->friendlyUrlSlugBatchLoader->getSlugsIndexedByEntityIdOnDomain(
                $blogCategoryIdsVisibleOnDomain,
                BlogCategoryDetailFriendlyUrlDataProvider::ROUTE_NAME,
                $domainId,
            );
        }

        return $slugsIndexedByDomainIdAndBlogCategoryId;
    }

    protected function createHreflangLink(int $domainId, string $slug): HreflangLink
    {
        $domainConfig = $this->domain->getDomainConfigById($domainId);

        return new HreflangLink($domainConfig->getLocale(), $domainConfig->getUrl() . $slug, $domainId);
    }
}
