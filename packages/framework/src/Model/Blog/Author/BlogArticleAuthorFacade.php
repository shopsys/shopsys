<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Author;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;

class BlogArticleAuthorFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly BlogArticleAuthorRepository $blogArticleAuthorRepository,
        protected readonly BlogArticleAuthorFactory $blogArticleAuthorFactory,
        protected readonly ImageFacade $imageFacade,
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

        return $blogArticleAuthor;
    }

    public function deleteById(int $blogArticleAuthorId): void
    {
        $blogArticleAuthor = $this->blogArticleAuthorRepository->getById($blogArticleAuthorId);

        $this->em->remove($blogArticleAuthor);
        $this->em->flush();
    }
}
