<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Category;

use App\FrontendApi\Model\Resolver\Category\PromotedCategory\PromotedCategoryFacade;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class PromotedCategoriesResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \App\FrontendApi\Model\Resolver\Category\PromotedCategory\PromotedCategoryFacade
     */
    private PromotedCategoryFacade $promotedCategoryFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected Domain $domain;

    /**
     * @param \App\FrontendApi\Model\Resolver\Category\PromotedCategory\PromotedCategoryFacade $promotedCategoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        PromotedCategoryFacade $promotedCategoryFacade,
        Domain $domain
    ) {
        $this->promotedCategoryFacade = $promotedCategoryFacade;
        $this->domain = $domain;
    }

    /**
     * @return \App\Model\Category\Category[]
     */
    public function resolve(): array
    {
        return $this->promotedCategoryFacade->getVisiblePromotedCategoriesOnDomain($this->domain->getCurrentDomainConfig());
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return ['resolve' => 'promotedCategoriesResolver'];
    }
}
