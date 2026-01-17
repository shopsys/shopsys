<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class BlogArticleFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(BlogArticleData $data): BlogArticle
    {
        $entityClass = $this->entityNameResolver->resolve(BlogArticle::class);

        return new $entityClass($data);
    }
}
