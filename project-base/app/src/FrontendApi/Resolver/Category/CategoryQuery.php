<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Category;

use App\Component\Deprecation\DeprecatedMethodException;
use App\Component\Router\FriendlyUrl\FriendlyUrlFacade as AppFriendlyUrlFacade;
use App\FrontendApi\Model\Product\Filter\ProductFilterFacade;
use App\FrontendApi\Resolver\Category\Exception\ReadyCategorySeoMixNotFoundUserError;
use App\FrontendApi\Resolver\Products\ProductsQuery;
use App\Model\Category\Category;
use App\Model\CategorySeo\Exception\ReadyCategorySeoMixNotFoundException;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use App\Model\CategorySeo\ReadyCategorySeoMixFacade;
use App\Model\Product\Flag\Flag;
use App\Model\Product\Parameter\Exception\ParameterValueNotFoundException;
use App\Model\Product\Parameter\ParameterFacade;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use Shopsys\FrameworkBundle\Model\Category\Category as BaseCategory;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Category\Exception\CategoryNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Exception\ParameterNotFoundException;
use Shopsys\FrontendApiBundle\Model\Error\InvalidArgumentUserError;
use Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoryQuery as BaseCategoryQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Category\Exception\CategoryNotFoundUserError;

/**
 * @method \App\Model\Category\Category getByUuid(string $uuid)
 * @method \App\Model\Category\Category getVisibleOnDomainAndSlug(string $urlSlug)
 * @property \App\Model\Category\CategoryFacade $categoryFacade
 */
class CategoryQuery extends BaseCategoryQuery
{
    /**
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \App\FrontendApi\Model\Product\Filter\ProductFilterFacade $productFilterFacade
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $appFriendlyUrlFacade
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     */
    public function __construct(
        CategoryFacade $categoryFacade,
        Domain $domain,
        FriendlyUrlFacade $friendlyUrlFacade,
        private readonly ProductFilterFacade $productFilterFacade,
        private readonly AppFriendlyUrlFacade $appFriendlyUrlFacade,
        private readonly ReadyCategorySeoMixFacade $readyCategorySeoMixFacade,
        private readonly ParameterFacade $parameterFacade,
    ) {
        parent::__construct($categoryFacade, $domain, $friendlyUrlFacade);
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated This method is deprecated, use categoryOrSeoMixByUuidOrUrlSlugQuery instead
     */
    public function categoryByUuidOrUrlSlugQuery(?string $uuid = null, ?string $urlSlug = null): BaseCategory
    {
        throw new DeprecatedMethodException();
    }

    /**
     * @param \GraphQL\Type\Definition\ResolveInfo $info
     * @param string|null $uuid
     * @param string|null $urlSlug
     * @return \App\Model\Category\Category|\App\Model\CategorySeo\ReadyCategorySeoMix
     */
    public function categoryOrSeoMixByUuidOrUrlSlugQuery(
        ResolveInfo $info,
        ?string $uuid = null,
        ?string $urlSlug = null,
    ): Category|ReadyCategorySeoMix {
        if ($uuid !== null) {
            return $this->getByUuid($uuid);
        }

        if ($urlSlug !== null) {
            $urlSlug = ltrim($urlSlug, '/');

            $friendlyUrl = $this->appFriendlyUrlFacade->findByDomainIdAndSlug(
                $this->domain->getId(),
                $urlSlug,
            );

            if ($friendlyUrl === null) {
                $modifiedSlug = TransformString::addOrRemoveTrailingSlashFromString($urlSlug);
                $friendlyUrl = $this->appFriendlyUrlFacade->findByDomainIdAndSlug(
                    $this->domain->getId(),
                    $modifiedSlug,
                );

                if ($friendlyUrl === null) {
                    throw new CategoryNotFoundUserError('Category with URL slug `' . $urlSlug . '` does not exist.');
                }
            }

            $entityClass = $this->appFriendlyUrlFacade->getEntityClassByRouteName($friendlyUrl->getRouteName());

            if ($entityClass === Category::class) {
                try {
                    $category = $this->categoryFacade->getVisibleOnDomainById($this->domain->getId(), $friendlyUrl->getEntityId());
                } catch (CategoryNotFoundException) {
                    throw new CategoryNotFoundUserError('Category with URL slug `' . $urlSlug . '` does not exist.');
                }

                $matchingReadyCategorySeoMix = $this->findMatchingReadyCategorySeoMix($info, $category);

                return $matchingReadyCategorySeoMix ?? $category;
            }

            if ($entityClass === ReadyCategorySeoMix::class) {
                try {
                    $readyCategorySeoMix = $this->readyCategorySeoMixFacade->getById($friendlyUrl->getEntityId());
                } catch (ReadyCategorySeoMixNotFoundException) {
                    throw new ReadyCategorySeoMixNotFoundUserError(sprintf('ReadyCategorySeoMix with URL slug "%s" does not exist.', $urlSlug));
                }

                $matchingReadyCategorySeoMix = $this->findMatchingReadyCategorySeoMix($info, $readyCategorySeoMix->getCategory());

                if (
                    $matchingReadyCategorySeoMix !== $readyCategorySeoMix &&
                    ($this->isFilterSet($info) || $this->isSortingDifferentFromReadyCategorySeoMix($info, $readyCategorySeoMix))
                ) {
                    return $matchingReadyCategorySeoMix ?? $readyCategorySeoMix->getCategory();
                }

                return $readyCategorySeoMix;
            }

            throw new CategoryNotFoundUserError('Category with URL slug `' . $urlSlug . '` does not exist.');
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

        return $this->categoryFacade->getCategoriesOfProductByFilterData($productFilterData);
    }

    /**
     * @param \GraphQL\Type\Definition\ResolveInfo $info
     * @param \App\Model\Category\Category $category
     * @return \App\Model\CategorySeo\ReadyCategorySeoMix|null
     */
    private function findMatchingReadyCategorySeoMix(ResolveInfo $info, Category $category): ?ReadyCategorySeoMix
    {
        $variableValues = $info->variableValues;

        try {
            return $this->readyCategorySeoMixFacade->findReadyCategorySeoMixByQueryInputData(
                $category->getId(),
                $variableValues['filter']['parameters'] ?? [],
                $variableValues['filter']['flags'] ?? [],
                $variableValues['orderingMode'] ?? ProductsQuery::getDefaultOrderingModeForListing(),
            );
        } catch (ParameterValueNotFoundException | ParameterNotFoundException) {
            return null;
        }
    }

    /**
     * @param \GraphQL\Type\Definition\ResolveInfo $resolveInfo
     * @return bool
     */
    private function isFilterSet(ResolveInfo $resolveInfo): bool
    {
        $variableValues = $resolveInfo->variableValues;
        $parametersVariable = $variableValues['filter']['parameters'] ?? [];

        $parameterUuids = array_map(
            static fn (array $parameterVariable) => $parameterVariable['parameter'],
            $parametersVariable,
        );
        $parameters = $this->parameterFacade->getParametersByUuids($parameterUuids);

        foreach ($parameters as $parameter) {
            if (!$parameter->isSlider()) {
                return true;
            }
        }

        $flags = $variableValues['filter']['flags'] ?? [];

        return count($flags) > 0;
    }

    /**
     * @param \GraphQL\Type\Definition\ResolveInfo $resolveInfo
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix $readyCategorySeoMix
     * @return bool
     */
    private function isSortingDifferentFromReadyCategorySeoMix(
        ResolveInfo $resolveInfo,
        ReadyCategorySeoMix $readyCategorySeoMix,
    ): bool {
        $variableValues = $resolveInfo->variableValues;
        $sorting = $variableValues['orderingMode'] ?? null;

        if ($sorting === null) {
            return false;
        }

        return strtolower($sorting) !== strtolower($readyCategorySeoMix->getOrdering());
    }
}
