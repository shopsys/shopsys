<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;

class BlogArticleBlogCategoryDomainFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle $blogArticle
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory $blogCategory
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleBlogCategoryDomain
     */
    public function create(
        BlogArticle $blogArticle,
        BlogCategory $blogCategory,
        int $domainId,
    ): BlogArticleBlogCategoryDomain {
        $entityClass = $this->entityNameResolver->resolve(BlogArticleBlogCategoryDomain::class);

        return new $entityClass($blogArticle, $blogCategory, $domainId);
    }
}
