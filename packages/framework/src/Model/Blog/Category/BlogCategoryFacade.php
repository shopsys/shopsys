<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Category;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleFacade;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleExportQueueFacade;
use Shopsys\FrameworkBundle\Model\Blog\BlogVisibilityRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Blog\Category\Exception\BlogCategoryNotFoundException;

class BlogCategoryFacade
{
    protected const INCREMENT_DUE_TO_MISSING_ROOT_CATEGORY = 1;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryRepository $blogCategoryRepository
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFactory $blogCategoryFactory
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryWithPreloadedChildrenFactory $blogCategoryWithPreloadedChildrenFactory
     * @param \Shopsys\FrameworkBundle\Model\Blog\BlogVisibilityRecalculationScheduler $blogVisibilityRecalculationScheduler
     * @param \Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleExportQueueFacade $blogArticleExportQueueFacade
     * @param \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleFacade $blogArticleFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade $cleanStorefrontCacheFacade
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly BlogCategoryRepository $blogCategoryRepository,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly ImageFacade $imageFacade,
        protected readonly BlogCategoryFactory $blogCategoryFactory,
        protected readonly BlogCategoryWithPreloadedChildrenFactory $blogCategoryWithPreloadedChildrenFactory,
        protected readonly BlogVisibilityRecalculationScheduler $blogVisibilityRecalculationScheduler,
        protected readonly BlogArticleExportQueueFacade $blogArticleExportQueueFacade,
        protected readonly BlogArticleFacade $blogArticleFacade,
        protected readonly Domain $domain,
        protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
    ) {
    }

    /**
     * @param int $blogCategoryId
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory
     */
    public function getById(int $blogCategoryId): BlogCategory
    {
        return $this->blogCategoryRepository->getById($blogCategoryId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryData $blogCategoryData
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory
     */
    public function create(BlogCategoryData $blogCategoryData): BlogCategory
    {
        $rootCategory = $this->getRootBlogCategory();
        $blogCategory = $this->blogCategoryFactory->create($blogCategoryData, $rootCategory);

        $this->em->persist($blogCategory);
        $this->em->flush();

        $blogCategory->createDomains($blogCategoryData);

        $this->friendlyUrlFacade->createFriendlyUrls('front_blogcategory_detail', $blogCategory->getId(), $blogCategory->getNames());

        $this->imageFacade->manageImages($blogCategory, $blogCategoryData->image);
        $this->blogVisibilityRecalculationScheduler->scheduleRecalculation();

        $this->em->flush();

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::BLOG_CATEGORIES_QUERY_KEY_PART);

        return $blogCategory;
    }

    /**
     * @param int $blogCategoryId
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryData $blogCategoryData
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory
     */
    public function edit(int $blogCategoryId, BlogCategoryData $blogCategoryData): BlogCategory
    {
        $rootCategory = $this->getRootBlogCategory();
        $blogCategory = $this->blogCategoryRepository->getById($blogCategoryId);
        $blogCategory->edit($blogCategoryData);

        if ($blogCategory->getParent() === null) {
            $blogCategory->setParent($rootCategory);
        }

        $this->em->flush();

        $this->friendlyUrlFacade->saveUrlListFormData('front_blogcategory_detail', $blogCategory->getId(), $blogCategoryData->urls);
        $this->friendlyUrlFacade->createFriendlyUrls('front_blogcategory_detail', $blogCategory->getId(), $blogCategory->getNames());

        $this->imageFacade->manageImages($blogCategory, $blogCategoryData->image);
        $this->blogVisibilityRecalculationScheduler->scheduleRecalculation();

        $this->scheduleArticlesToExportByCategory($blogCategory);

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::BLOG_CATEGORIES_QUERY_KEY_PART);

        return $blogCategory;
    }

    /**
     * @param int $blogCategoryId
     */
    public function deleteById(int $blogCategoryId): void
    {
        $blogCategory = $this->blogCategoryRepository->getById($blogCategoryId);

        foreach ($blogCategory->getChildren() as $child) {
            $child->setParent($blogCategory->getParent());
        }

        $this->em->flush();
        $this->em->remove($blogCategory);
        $this->blogVisibilityRecalculationScheduler->scheduleRecalculation();
        $this->scheduleArticlesToExportByCategory($blogCategory);
        $this->em->flush();

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::BLOG_CATEGORIES_QUERY_KEY_PART);
    }

    /**
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[]
     */
    public function getTranslatedAll(string $locale): array
    {
        return $this->blogCategoryRepository->getAllByLocale($locale);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[] $selectedCategories
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[]
     */
    public function getAllBlogCategoriesOfCollapsedTree(array $selectedCategories): array
    {
        return $this->blogCategoryRepository->getAllBlogCategoriesOfCollapsedTree($selectedCategories);
    }

    /**
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryWithPreloadedChildren[]
     */
    public function getAllBlogCategoriesWithPreloadedChildren(string $locale): array
    {
        $blogCategories = $this->blogCategoryRepository->getPreOrderTreeTraversalForAllBlogCategories($locale);

        return $this->blogCategoryWithPreloadedChildrenFactory->createBlogCategoriesWithPreloadedChildren($blogCategories);
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryWithPreloadedChildren[]
     */
    public function getVisibleBlogCategoriesWithPreloadedChildrenOnDomain(int $domainId, string $locale): array
    {
        $blogCategories = $this->blogCategoryRepository->getPreOrderTreeTraversalForVisibleBlogCategoriesOnDomain($domainId, $locale);

        return $this->blogCategoryWithPreloadedChildrenFactory->createBlogCategoriesWithPreloadedChildren($blogCategories);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory $blogCategory
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[]
     */
    public function getVisibleBlogCategoriesInPathFromRootOnDomain(BlogCategory $blogCategory, int $domainId): array
    {
        return $this->blogCategoryRepository->getVisibleBlogCategoriesInPathFromRootOnDomain($blogCategory, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory $category
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[]
     */
    public function getAllVisibleChildrenByBlogCategoryAndDomainId(BlogCategory $category, int $domainId): array
    {
        return $this->blogCategoryRepository->getAllVisibleChildrenByBlogCategoryAndDomainId($category, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory $blogCategory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[]
     */
    public function getTranslatedAllWithoutBranch(BlogCategory $blogCategory, DomainConfig $domainConfig): array
    {
        return $this->blogCategoryRepository->getTranslatedAllWithoutBranch($blogCategory, $domainConfig);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory
     */
    public function getRootBlogCategory(): BlogCategory
    {
        return $this->blogCategoryRepository->getRootBlogCategory();
    }

    /**
     * @param int $domainId
     * @param int $blogCategoryId
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory
     */
    public function getVisibleOnDomainById(int $domainId, int $blogCategoryId): BlogCategory
    {
        $blogCategory = $this->getById($blogCategoryId);

        if (!$blogCategory->isVisible($domainId)) {
            $message = 'Blog category ID ' . $blogCategoryId . ' is not visible on domain ID ' . $domainId;

            throw new BlogCategoryNotFoundException($message);
        }

        return $blogCategory;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle $blogArticle
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory
     */
    public function getBlogArticleMainBlogCategoryOnDomain(BlogArticle $blogArticle, int $domainId): BlogCategory
    {
        return $this->blogCategoryRepository->getBlogArticleMainBlogCategoryOnDomain($blogArticle, $domainId);
    }

    /**
     * @param int[] $blogCategoryIds
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[]
     */
    public function getByIds(array $blogCategoryIds): array
    {
        return $this->blogCategoryRepository->getByIds($blogCategoryIds);
    }

    /**
     * @param int $domainId
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory
     */
    public function getVisibleByUuid(int $domainId, string $uuid): BlogCategory
    {
        return $this->blogCategoryRepository->getVisibleByUuid($domainId, $uuid);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory $blogCategory
     */
    protected function scheduleArticlesToExportByCategory(BlogCategory $blogCategory): void
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $articleIds = $this->blogArticleFacade->getBlogArticleIdsByCategory(
                $blogCategory,
                $domainConfig->getId(),
                $domainConfig->getLocale(),
            );

            $this->blogArticleExportQueueFacade->addIdsBatch(
                $articleIds,
                $domainConfig->getId(),
            );
        }
    }

    /**
     * @param array<int, array{id: string|int, parent_id: string|int|null, depth: int, left: int, right: int}> $blogCategoriesOrderingData
     */
    public function reorderByNestedSetValues(array $blogCategoriesOrderingData): void
    {
        $rootCategoryId = $this->getRootBlogCategory()->getId();

        $query = $this->em->createQuery('
            UPDATE ' . BlogCategory::class . ' bc 
            SET bc.parent = :parent, bc.level = :level, bc.lft = :lft, bc.rgt = :rgt 
            WHERE bc.id = :id
        ');

        foreach ($blogCategoriesOrderingData as $categoryOrderingData) {
            $query->execute([
                'id' => (int)$categoryOrderingData['id'],
                'parent' => $categoryOrderingData['parent_id'] ? (int)$categoryOrderingData['parent_id'] : $rootCategoryId,
                'level' => $categoryOrderingData['depth'] + static::INCREMENT_DUE_TO_MISSING_ROOT_CATEGORY,
                'lft' => $categoryOrderingData['left'] + static::INCREMENT_DUE_TO_MISSING_ROOT_CATEGORY,
                'rgt' => $categoryOrderingData['right'] + static::INCREMENT_DUE_TO_MISSING_ROOT_CATEGORY,
            ]);
        }

        foreach ($this->domain->getAllIds() as $domainId) {
            $allIds = array_map(
                static fn (BlogArticle $blogArticle) => $blogArticle->getId(),
                $this->blogArticleFacade->getAllByDomainId($domainId),
            );

            $this->blogArticleExportQueueFacade->addIdsBatch($allIds, $domainId);
        }

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::BLOG_CATEGORIES_QUERY_KEY_PART);
    }

    /**
     * @return int|null
     */
    public function findVisibleMainBlogCategoryIdOnCurrentDomain(): ?int
    {
        return $this->blogCategoryRepository->findVisibleMainBlogCategoryIdOnDomain($this->domain->getId());
    }
}
