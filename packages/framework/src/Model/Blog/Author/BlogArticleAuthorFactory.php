<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Author;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class BlogArticleAuthorFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(BlogArticleAuthorData $data): BlogArticleAuthor
    {
        $entityClassName = $this->entityNameResolver->resolve(BlogArticleAuthor::class);

        return new $entityClassName($data);
    }
}
