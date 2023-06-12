<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Category;

use App\FrontendApi\Model\Product\Filter\ProductFilterFacade;
use App\Model\Category\Category;
use App\Model\Product\Flag\Flag;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrontendApiBundle\Model\Error\InvalidArgumentUserError;
use Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoryQuery as BaseCategoryQuery;

class CategoryQuery extends BaseCategoryQuery
{
    /**
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \App\FrontendApi\Model\Product\Filter\ProductFilterFacade $productFilterFacade
     */
    public function __construct(
        CategoryFacade $categoryFacade,
        Domain $domain,
        FriendlyUrlFacade $friendlyUrlFacade,
        private readonly ProductFilterFacade $productFilterFacade,
    ) {
        parent::__construct($categoryFacade, $domain, $friendlyUrlFacade);
    }

    /**
     * @param string|null $uuid
     * @param string|null $urlSlug
     * @return \App\Model\Category\Category
     */
    public function categoryByUuidOrUrlSlugQuery(?string $uuid = null, ?string $urlSlug = null): Category
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

        throw new InvalidArgumentUserError('You need to provide argument \'uuid\' or \'urlSlug\'.');
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \App\Model\Product\Flag\Flag $flag
     * @return \App\Model\Category\Category[]
     */
    public function categoriesFilteredByProductFilterForFlagQuery(Argument $argument, Flag $flag): array
    {
        $argument['filter'] = $argument['productFilter'];

        $productFilterData = $this->productFilterFacade->getValidatedProductFilterDataForFlag(
            $argument,
            $flag,
        );

        $productFilterData->flags = [$flag];

        return $this->categoryFacade->getCategoriesOfProductByFilterData($productFilterData); // @phpstan-ignore-line
    }
}
