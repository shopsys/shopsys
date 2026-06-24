<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Author;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleRepository;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleExportScheduler;

class BlogArticleAuthorFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly BlogArticleAuthorRepository $blogArticleAuthorRepository,
        protected readonly BlogArticleAuthorFactory $blogArticleAuthorFactory,
        protected readonly ImageFacade $imageFacade,
        protected readonly BlogArticleRepository $blogArticleRepository,
        protected readonly BlogArticleExportScheduler $blogArticleExportScheduler,
    ) {
    }

    public function getById(int $blogArticleAuthorId): BlogArticleAuthor
    {
        return $this->blogArticleAuthorRepository->getById($blogArticleAuthorId);
    }

    public function findById(int $blogArticleAuthorId): ?BlogArticleAuthor
    {
        return $this->blogArticleAuthorRepository->findById($blogArticleAuthorId);
    }

    public function create(BlogArticleAuthorData $blogArticleAuthorData): BlogArticleAuthor
    {
        $blogArticleAuthor = $this->blogArticleAuthorFactory->create($blogArticleAuthorData);

        $this->em->persist($blogArticleAuthor);
        $this->em->flush();

        $this->imageFacade->manageImages($blogArticleAuthor, $blogArticleAuthorData->image);
        $this->em->flush();

        return $blogArticleAuthor;
    }

    public function edit(int $blogArticleAuthorId, BlogArticleAuthorData $blogArticleAuthorData): BlogArticleAuthor
    {
        $blogArticleAuthor = $this->blogArticleAuthorRepository->getById($blogArticleAuthorId);
        $blogArticleAuthor->edit($blogArticleAuthorData);

        $this->imageFacade->manageImages($blogArticleAuthor, $blogArticleAuthorData->image);
        $this->em->flush();

        $this->blogArticleExportScheduler->scheduleRowIdsForImmediateExport(
            $this->blogArticleRepository->getBlogArticleIdsByBlogArticleAuthor($blogArticleAuthor),
        );

        return $blogArticleAuthor;
    }

    public function deleteById(int $blogArticleAuthorId): void
    {
        $blogArticleAuthor = $this->blogArticleAuthorRepository->getById($blogArticleAuthorId);
        $affectedBlogArticleIds = $this->blogArticleRepository->getBlogArticleIdsByBlogArticleAuthor($blogArticleAuthor);

        $this->em->remove($blogArticleAuthor);
        $this->em->flush();

        $this->blogArticleExportScheduler->scheduleRowIdsForImmediateExport($affectedBlogArticleIds);
    }
}
