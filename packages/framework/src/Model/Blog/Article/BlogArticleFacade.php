<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Workflow\TransitionNameByTargetPlaceResolver;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleExportScheduler;
use Shopsys\FrameworkBundle\Model\Blog\Article\Exception\BlogArticleStatusTransitionException;
use Shopsys\FrameworkBundle\Model\Blog\BlogVisibilityRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Symfony\Component\Workflow\Exception\NotEnabledTransitionException;
use Symfony\Component\Workflow\WorkflowInterface;

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
        protected readonly TransitionNameByTargetPlaceResolver $transitionNameByTargetPlaceResolver,
        protected readonly WorkflowInterface $blogArticlePublishingStateMachine,
        protected readonly Domain $domain,
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

        $blogArticle->setCategories($this->blogArticleBlogCategoryDomainFactory, $blogArticleData->blogCategoriesByDomainId);
        $blogArticle->createDomains($blogArticleData);

        $this->applyStatusTransitions($blogArticle, $blogArticleData);

        $this->em->persist($blogArticle);
        $this->em->flush();

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

        try {
            $this->applyStatusTransitions($blogArticle, $blogArticleData);
        } catch (BlogArticleStatusTransitionException $e) {
            $this->em->refresh($blogArticle);

            throw $e;
        }

        $this->em->flush();

        $this->friendlyUrlFacade->saveUrlListFormData('front_blogarticle_detail', $blogArticle->getId(), $blogArticleData->urls);
        $this->friendlyUrlFacade->createFriendlyUrls('front_blogarticle_detail', $blogArticleId, $blogArticle->getNames());

        $this->imageFacade->manageImages($blogArticle, $blogArticleData->image);
        $this->blogVisibilityRecalculationScheduler->scheduleRecalculation();

        $this->em->flush();

        $this->blogArticleExportScheduler->scheduleRowIdForImmediateExport($blogArticle->getId());

        return $blogArticle;
    }

    protected function applyStatusTransitions(
        BlogArticle $blogArticle,
        BlogArticleData $blogArticleData,
    ): void {
        foreach ($blogArticle->getDomains() as $blogArticleDomain) {
            $domainConfig = $this->domain->getDomainConfigById($blogArticleDomain->getDomainId());
            $desiredStatus = $blogArticleData->statuses[$domainConfig->getId()];

            if ($blogArticleDomain->getStatus() === $desiredStatus) {
                continue;
            }

            try {
                $transitionName = $this->transitionNameByTargetPlaceResolver
                    ->getTransitionNameForTargetPlace($this->blogArticlePublishingStateMachine, $blogArticleDomain, $desiredStatus);
                $this->blogArticlePublishingStateMachine->apply($blogArticleDomain, $transitionName);
            } catch (NotEnabledTransitionException $e) {
                $messages = [];

                foreach ($e->getTransitionBlockerList() as $blocker) {
                    $messages[] = $blocker->getMessage();
                }

                throw new BlogArticleStatusTransitionException($domainConfig->getName(), $messages, $e);
            }
        }
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
