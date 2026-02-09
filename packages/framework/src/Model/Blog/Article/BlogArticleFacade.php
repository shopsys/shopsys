<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleExportScheduler;
use Shopsys\FrameworkBundle\Model\Blog\BlogVisibilityRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;

class BlogArticleFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly BlogArticleRepository $blogArticleRepository,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly BlogArticleFactory $blogArticleFactory,
        protected readonly BlogArticleBlogCategoryDomainFactory $blogArticleBlogCategoryDomainFactory,
        protected readonly ImageFacade $imageFacade,
        protected readonly BlogVisibilityRecalculationScheduler $blogVisibilityRecalculationScheduler,
        protected readonly BlogArticleExportScheduler $blogArticleExportScheduler,
    ) {
    }

    public function getById(int $blogArticleId): BlogArticle
    {
        return $this->blogArticleRepository->getById($blogArticleId);
    }

    public function getAllArticlesCountByDomainId(int $domainId): int
    {
        return $this->blogArticleRepository->getAllBlogArticlesCountByDomainId($domainId);
    }

    public function create(BlogArticleData $blogArticleData): BlogArticle
    {
        $blogArticle = $this->blogArticleFactory->create($blogArticleData);

        $this->em->persist($blogArticle);
        $this->em->flush();

        $blogArticle->setCategories($this->blogArticleBlogCategoryDomainFactory, $blogArticleData->blogCategoriesByDomainId);
        $blogArticle->createDomains($blogArticleData);

        $this->friendlyUrlFacade->createFriendlyUrls('front_blogarticle_detail', $blogArticle->getId(), $blogArticle->getNames());

        $this->imageFacade->manageImages($blogArticle, $blogArticleData->image);
        $this->blogVisibilityRecalculationScheduler->scheduleRecalculation();

        $this->em->flush();

        $this->blogArticleExportScheduler->scheduleRowIdForImmediateExport($blogArticle->getId());

        return $blogArticle;
    }

    public function edit(int $blogArticleId, BlogArticleData $blogArticleData): BlogArticle
    {
        $blogArticle = $this->blogArticleRepository->getById($blogArticleId);
        $blogArticle->edit($blogArticleData, $this->blogArticleBlogCategoryDomainFactory);

        $this->em->flush();

        $this->friendlyUrlFacade->saveUrlListFormData('front_blogarticle_detail', $blogArticle->getId(), $blogArticleData->urls);
        $this->friendlyUrlFacade->createFriendlyUrls('front_blogarticle_detail', $blogArticleId, $blogArticle->getNames());

        $this->imageFacade->manageImages($blogArticle, $blogArticleData->image);
        $this->blogVisibilityRecalculationScheduler->scheduleRecalculation();

        $this->em->flush();

        $this->blogArticleExportScheduler->scheduleRowIdForImmediateExport($blogArticle->getId());

        return $blogArticle;
    }

    public function delete(int $blogArticleId): void
    {
        $blogArticle = $this->blogArticleRepository->getById($blogArticleId);

        $this->em->remove($blogArticle);
        $this->blogVisibilityRecalculationScheduler->scheduleRecalculation();
        $this->em->flush();

        $this->blogArticleExportScheduler->scheduleRowIdForImmediateExport($blogArticleId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle[]
     */
    public function getAllByDomainId(int $domainId): array
    {
        return $this->blogArticleRepository->getAllByDomainId($domainId);
    }

    /**
     * @return int[]
     */
    public function getAllIdsByDomainId(int $domainId): array
    {
        return $this->blogArticleRepository->getAllIdsByDomainId($domainId);
    }

    /**
     * @return int[]
     */
    public function getBlogArticleIdsByCategory(BlogCategory $blogCategory, int $domainId, string $locale): array
    {
        return $this->blogArticleRepository->getBlogArticleIdsByCategory(
            $blogCategory,
            $domainId,
            $locale,
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle[]
     */
    public function getAllVisibleOnDomain(DomainConfig $domainConfig): array
    {
        return $this->blogArticleRepository->getAllVisibleOnDomain($domainConfig);
    }

    public function getQueryBuilderForQuickSearch(
        ?int $selectedDomainId,
        QuickSearchFormData $searchData,
        string $locale,
    ): QueryBuilder {
        return $this->blogArticleRepository->getQueryBuilderForQuickSearch($selectedDomainId, $searchData, $locale);
    }
}
