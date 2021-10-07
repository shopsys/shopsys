<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Category;

use App\FrontendApi\Model\Product\Filter\ProductFilterFacade;
use App\Model\Product\Flag\Flag;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoryResolver as BaseCategoryResolver;

/**
 * @property \App\Model\Category\CategoryFacade $categoryFacade
 * @method \App\Model\Category\Category resolver(string $uuid)
 * @method \App\Model\Category\Category getByUuid(string $uuid)
 * @method \App\Model\Category\Category getVisibleOnDomainAndSlug(string $urlSlug)
 */
class CategoryResolver extends BaseCategoryResolver
{
    /**
     * @var \App\FrontendApi\Model\Product\Filter\ProductFilterFacade
     */
    private ProductFilterFacade $productFilterFacade;

    /**
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain|null $domain
     * @param \Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade|null $friendlyUrlFacade
     * @param \App\FrontendApi\Model\Product\Filter\ProductFilterFacade $productFilterFacade
     */
    public function __construct(
        CategoryFacade $categoryFacade,
        ?Domain $domain = null,
        ?FriendlyUrlFacade $friendlyUrlFacade = null,
        ProductFilterFacade $productFilterFacade
    ) {
        parent::__construct($categoryFacade, $domain, $friendlyUrlFacade);

        $this->productFilterFacade = $productFilterFacade;
    }

    /**
     * @param string|null $uuid
     * @param string|null $urlSlug
     * @return \App\Model\Category\Category
     */
    public function resolveByUuidOrUrlSlug(?string $uuid = null, ?string $urlSlug = null): Category
    {
        if ($uuid !== null) {
            /** @var \App\Model\Category\Category $category */
            $category = $this->getByUuid($uuid);

            return $category;
        }

        if ($urlSlug !== null) {
            $urlSlug = ltrim($urlSlug, '/');

            /** @var \App\Model\Category\Category $category */
            $category = $this->getVisibleOnDomainAndSlug($urlSlug);

            return $category;
        }

        throw new UserError('You need to provide argument \'uuid\' or \'urlSlug\'.');
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \App\Model\Product\Flag\Flag $flag
     * @return \App\Model\Category\Category[]
     */
    public function categoriesFilteredByProductFilterForFlag(Argument $argument, Flag $flag): array
    {
        $argument['filter'] = $argument['productFilter'];

        $productFilterData = $this->productFilterFacade->getValidatedProductFilterDataForFlag(
            $argument,
            $flag
        );

        $productFilterData->flags = [$flag];

        return $this->categoryFacade->getCategoriesOfProductByFilterData($productFilterData);
    }
}
