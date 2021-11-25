<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Blog\Category;

use App\Model\Blog\Category\BlogCategoryFacade;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Shopsys\Cdn\Component\Domain\Domain;

class BlogCategoriesResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \App\Model\Blog\Category\BlogCategoryFacade
     */
    private BlogCategoryFacade $blogCategoryFacade;

    /**
     * @var \Shopsys\Cdn\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @param \App\Model\Blog\Category\BlogCategoryFacade $blogCategoryFacade
     * @param \Shopsys\Cdn\Component\Domain\Domain $domain
     */
    public function __construct(BlogCategoryFacade $blogCategoryFacade, Domain $domain)
    {
        $this->blogCategoryFacade = $blogCategoryFacade;
        $this->domain = $domain;
    }

    /**
     * @return \App\Model\Blog\Category\BlogCategory[]
     */
    public function resolve(): array
    {
        return $this->blogCategoryFacade->getAllVisibleChildrenByBlogCategoryAndDomainId(
            $this->blogCategoryFacade->getRootBlogCategory(),
            $this->domain->getId()
        );
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return [
            'resolve' => 'blogCategories',
        ];
    }
}
