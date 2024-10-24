<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Category;

use GraphQL\Type\Definition\ResolveInfo;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Category\Exception\CategoryNotFoundException;
use Shopsys\FrameworkBundle\Model\CategorySeo\Exception\ReadyCategorySeoMixNotFoundException;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixFacade;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Exception\ParameterNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Exception\ParameterValueNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;
use Shopsys\FrontendApiBundle\Model\Error\InvalidArgumentUserError;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Category\Exception\CategoryNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Resolver\Category\Exception\ReadyCategorySeoMixNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductOrderingModeProvider;

class CategoryQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade $productFilterFacade
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductOrderingModeProvider $productOrderingModeProvider
     */
    public function __construct(
        protected readonly CategoryFacade $categoryFacade,
        protected readonly Domain $domain,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly ReadyCategorySeoMixFacade $readyCategorySeoMixFacade,
        protected readonly ParameterFacade $parameterFacade,
        protected readonly ProductFilterFacade $productFilterFacade,
        protected readonly ProductOrderingModeProvider $productOrderingModeProvider,
    ) {
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    protected function getByUuid(string $uuid): Category
    {
        try {
            return $this->categoryFacade->getByUuid($uuid);
        } catch (CategoryNotFoundException $categoryNotFoundException) {
            throw new CategoryNotFoundUserError($categoryNotFoundException->getMessage());
        }
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\Flag $flag
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
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
     * @param string|null $uuid
     * @param string|null $urlSlug
     * @return \Shopsys\FrameworkBundle\Model\Category\Category|\Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix
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

            $friendlyUrl = $this->friendlyUrlFacade->findByDomainIdAndSlug(
                $this->domain->getId(),
                $urlSlug,
            );

            if ($friendlyUrl === null) {
                $modifiedSlug = TransformString::addOrRemoveTrailingSlashFromString($urlSlug);
                $friendlyUrl = $this->friendlyUrlFacade->findByDomainIdAndSlug(
                    $this->domain->getId(),
                    $modifiedSlug,
                );

                if ($friendlyUrl === null) {
                    throw new CategoryNotFoundUserError('Category with URL slug `' . $urlSlug . '` does not exist.');
                }
            }

            $entityClass = $this->friendlyUrlFacade->getEntityClassByRouteName($friendlyUrl->getRouteName());

            if (is_a($entityClass, Category::class, true)) {
                try {
                    $category = $this->categoryFacade->getVisibleOnDomainById($this->domain->getId(), $friendlyUrl->getEntityId());
                } catch (CategoryNotFoundException) {
                    throw new CategoryNotFoundUserError('Category with URL slug `' . $urlSlug . '` does not exist.');
                }

                $matchingReadyCategorySeoMix = $this->findMatchingReadyCategorySeoMix($info, $category);

                return $matchingReadyCategorySeoMix ?? $category;
            }

            if (is_a($entityClass, ReadyCategorySeoMix::class, true)) {
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
     * @param \GraphQL\Type\Definition\ResolveInfo $info
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @return \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix|null
     */
    protected function findMatchingReadyCategorySeoMix(ResolveInfo $info, Category $category): ?ReadyCategorySeoMix
    {
        $variableValues = $info->variableValues;

        try {
            return $this->readyCategorySeoMixFacade->findReadyCategorySeoMixByQueryInputData(
                $category->getId(),
                $variableValues['filter']['parameters'] ?? [],
                $variableValues['filter']['flags'] ?? [],
                $variableValues['orderingMode'] ?? $this->productOrderingModeProvider->getDefaultOrderingModeForListing(),
            );
        } catch (ParameterValueNotFoundException|ParameterNotFoundException) {
            return null;
        }
    }

    /**
     * @param \GraphQL\Type\Definition\ResolveInfo $resolveInfo
     * @return bool
     */
    protected function isFilterSet(ResolveInfo $resolveInfo): bool
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
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix $readyCategorySeoMix
     * @return bool
     */
    protected function isSortingDifferentFromReadyCategorySeoMix(
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
