<?php

declare(strict_types=1);

namespace App\Model\Blog\Article;

use App\Component\Image\ImageFacade;
use App\Model\Blog\Article\Elasticsearch\BlogArticleExportScheduler;
use App\Model\Blog\BlogVisibilityRecalculationScheduler;
use App\Model\Blog\Category\BlogCategory;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;

class BlogArticleFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Blog\Article\BlogArticleRepository $blogArticleRepository
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \App\Model\Blog\Article\BlogArticleFactory $blogArticleFactory
     * @param \App\Model\Blog\Article\BlogArticleBlogCategoryDomainFactory $blogArticleBlogCategoryDomainFactory
     * @param \App\Component\Image\ImageFacade $imageFacade
     * @param \App\Model\Blog\BlogVisibilityRecalculationScheduler $blogVisibilityRecalculationScheduler
     * @param \App\Model\Blog\Article\Elasticsearch\BlogArticleExportScheduler $blogArticleExportScheduler
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly BlogArticleRepository $blogArticleRepository,
        private readonly FriendlyUrlFacade $friendlyUrlFacade,
        private readonly BlogArticleFactory $blogArticleFactory,
        private readonly BlogArticleBlogCategoryDomainFactory $blogArticleBlogCategoryDomainFactory,
        private readonly ImageFacade $imageFacade,
        private readonly BlogVisibilityRecalculationScheduler $blogVisibilityRecalculationScheduler,
        private readonly BlogArticleExportScheduler $blogArticleExportScheduler,
    ) {
    }

    /**
     * @param int $blogArticleId
     * @return \App\Model\Blog\Article\BlogArticle
     */
    public function getById(int $blogArticleId): BlogArticle
    {
        return $this->blogArticleRepository->getById($blogArticleId);
    }

    /**
     * @param int $domainId
     * @return int
     */
    public function getAllArticlesCountByDomainId(int $domainId): int
    {
        return $this->blogArticleRepository->getAllBlogArticlesCountByDomainId($domainId);
    }

    /**
     * @param \App\Model\Blog\Article\BlogArticleData $blogArticleData
     * @return \App\Model\Blog\Article\BlogArticle
     */
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

    /**
     * @param int $blogArticleId
     * @param \App\Model\Blog\Article\BlogArticleData $blogArticleData
     * @return \App\Model\Blog\Article\BlogArticle
     */
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

    /**
     * @param int $blogArticleId
     */
    public function delete(int $blogArticleId): void
    {
        $blogArticle = $this->blogArticleRepository->getById($blogArticleId);

        $this->em->remove($blogArticle);
        $this->blogVisibilityRecalculationScheduler->scheduleRecalculation();
        $this->em->flush();

        $this->blogArticleExportScheduler->scheduleRowIdForImmediateExport($blogArticleId);
    }

    /**
     * @param int $domainId
     * @return \App\Model\Blog\Article\BlogArticle[]
     */
    public function getAllByDomainId(int $domainId): array
    {
        return $this->blogArticleRepository->getAllByDomainId($domainId);
    }

    /**
     * @param \App\Model\Blog\Category\BlogCategory $blogCategory
     * @param int $domainId
     * @param string $locale
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
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \App\Model\Blog\Article\BlogArticle[]
     */
    public function getAllVisibleOnDomain(DomainConfig $domainConfig): array
    {
        return $this->blogArticleRepository->getAllVisibleOnDomain($domainConfig);
    }
}
