<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Blog\Category;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class BlogCategoriesQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade $blogCategoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly BlogCategoryFacade $blogCategoryFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[]
     */
    public function blogCategoriesQuery(): array
    {
        return $this->blogCategoryFacade->getAllVisibleChildrenByBlogCategoryAndDomainId(
            $this->blogCategoryFacade->getRootBlogCategory(),
            $this->domain->getId(),
        );
    }
}
