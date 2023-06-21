<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Blog\Category;

use App\Model\Blog\Category\BlogCategoryFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class BlogCategoriesQuery extends AbstractQuery
{
    /**
     * @param \App\Model\Blog\Category\BlogCategoryFacade $blogCategoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly BlogCategoryFacade $blogCategoryFacade,
        private readonly Domain $domain,
    ) {
    }

    /**
     * @return \App\Model\Blog\Category\BlogCategory[]
     */
    public function blogCategoriesQuery(): array
    {
        return $this->blogCategoryFacade->getAllVisibleChildrenByBlogCategoryAndDomainId(
            $this->blogCategoryFacade->getRootBlogCategory(),
            $this->domain->getId(),
        );
    }
}
