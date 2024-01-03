<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class BlogArticleFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleData $data
     * @return \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle
     */
    public function create(BlogArticleData $data): BlogArticle
    {
        $entityClass = $this->entityNameResolver->resolve(BlogArticle::class);

        return new $entityClass($data);
    }
}
