<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;

class BlogArticleBlogCategoryDomainFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(
        BlogArticle $blogArticle,
        BlogCategory $blogCategory,
        int $domainId,
    ): BlogArticleBlogCategoryDomain {
        $entityClass = $this->entityNameResolver->resolve(BlogArticleBlogCategoryDomain::class);

        return new $entityClass($blogArticle, $blogCategory, $domainId);
    }
}
