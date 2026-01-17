<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Category;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class BlogCategoryFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(BlogCategoryData $data, ?BlogCategory $rootBlogCategory): BlogCategory
    {
        $entityClass = $this->entityNameResolver->resolve(BlogCategory::class);
        $blogCategory = new $entityClass($data);

        if ($rootBlogCategory !== null && $blogCategory->getParent() === null) {
            $blogCategory->setParent($rootBlogCategory);
        }

        return $blogCategory;
    }
}
