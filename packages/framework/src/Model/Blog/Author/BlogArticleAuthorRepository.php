<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Author;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper;
use Shopsys\FrameworkBundle\Model\Blog\Author\Exception\BlogArticleAuthorNotFoundException;

class BlogArticleAuthorRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly DatabaseSearchingHelper $databaseSearchingHelper,
    ) {
    }

    protected function getBlogArticleAuthorRepository(): EntityRepository
    {
        return $this->em->getRepository(BlogArticleAuthor::class);
    }

    public function getById(int $blogArticleAuthorId): BlogArticleAuthor
    {
        $blogArticleAuthor = $this->getBlogArticleAuthorRepository()->find($blogArticleAuthorId);

        if ($blogArticleAuthor === null) {
            throw new BlogArticleAuthorNotFoundException(
                'Blog article author with ID ' . $blogArticleAuthorId . ' not found.',
            );
        }

        return $blogArticleAuthor;
    }

    public function findById(int $blogArticleAuthorId): ?BlogArticleAuthor
    {
        return $this->getBlogArticleAuthorRepository()->find($blogArticleAuthorId);
    }

    public function getAllBlogArticleAuthorsQueryBuilder(?string $searchText): QueryBuilder
    {
        $queryBuilder = $this->getBlogArticleAuthorRepository()->createQueryBuilder('ba');

        if ($searchText !== null && $searchText !== '') {
            $queryBuilder
                ->andWhere('LOWER(ba.name) LIKE LOWER(:searchText)')
                ->setParameter('searchText', $this->databaseSearchingHelper->getFullTextLikeSearchString($searchText));
        }

        return $queryBuilder;
    }
}
