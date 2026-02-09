<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class ArticleFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(ArticleData $data): Article
    {
        $entityClassName = $this->entityNameResolver->resolve(Article::class);

        return new $entityClassName($data);
    }
}
