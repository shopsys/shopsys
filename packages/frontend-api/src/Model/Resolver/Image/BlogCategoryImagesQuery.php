<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Image;

use GraphQL\Executor\Promise\Promise;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;

class BlogCategoryImagesQuery extends ImagesQuery
{
    protected const string ENTITY_NAME = 'blogCategory';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory $blogCategory
     * @param string|null $type
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function mainImageByBlogCategoryPromiseQuery(BlogCategory $blogCategory, ?string $type): Promise
    {
        return $this->mainImageByEntityIdPromiseQuery($blogCategory->getId(), static::ENTITY_NAME, $type);
    }
}
