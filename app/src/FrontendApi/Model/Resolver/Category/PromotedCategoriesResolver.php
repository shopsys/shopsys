<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Category;

use App\Model\Category\TopCategory\TopCategoryFacade;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class PromotedCategoriesResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \App\Model\Category\TopCategory\TopCategoryFacade
     */
    protected TopCategoryFacade $topCategoryFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected Domain $domain;

    /**
     * @param \App\Model\Category\TopCategory\TopCategoryFacade $topCategoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        TopCategoryFacade $topCategoryFacade,
        Domain $domain
    ) {
        $this->topCategoryFacade = $topCategoryFacade;
        $this->domain = $domain;
    }

    /**
     * @return \App\Model\Category\Category[]
     */
    public function resolve(): array
    {
        return $this->topCategoryFacade->getVisibleCategoriesByDomainId($this->domain->getId());
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return ['resolve' => 'promotedCategoriesResolver'];
    }
}
