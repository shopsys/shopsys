<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Category;

use ArrayObject;
use GraphQL\Type\Definition\ResolveInfo;
use InvalidArgumentException;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade;

class CategoryResolverMap extends ResolverMap
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly HreflangLinksFacade $hreflangLinksFacade,
        protected readonly DataLoaderInterface $readyCategorySeoMixesBatchLoader,
        protected readonly DataLoaderInterface $categoryChildrenBatchLoader,
        protected readonly CategoryFacade $categoryFacade,
        protected readonly DataLoaderInterface $categorySlugBatchLoader,
        protected readonly DataLoaderInterface $categorySeoSlugBatchLoader,
    ) {
    }

    #[Override]
    protected function map(): array
    {
        return [
            'Category' => [
                self::RESOLVE_FIELD => function (Category|ReadyCategorySeoMix $value, ArgumentInterface $args, ArrayObject $context, ResolveInfo $info) {
                    if ($value instanceof Category) {
                        return $this->mapByCategory($info->fieldName, $value);
                    }

                    return $this->mapByReadyCategorySeoMix($info->fieldName, $value);
                },
            ],
        ];
    }

    protected function mapCommonFields(string $fieldName, Category $category): mixed
    {
        return match ($fieldName) {
            'id' => $category->getId(),
            'name' => $category->getName($this->domain->getLocale()) ?? '',
            'children' => $this->categoryChildrenBatchLoader->load($category),
            'parent' => $category->getParent() !== null && $category->getParent()->getParent() !== null ? $category->getParent() : null,
            'readyCategorySeoMixLinks' => $this->readyCategorySeoMixesBatchLoader->load($category->getId()),
            'categoryHierarchy' => $this->categoryFacade->getVisibleCategoriesInPathFromRootOnDomain($category, $this->domain->getId()),
            'hreflangLinks' => $this->hreflangLinksFacade->getForCategory($category, $this->domain->getId()),
            'automatedFilters' => $category->getAutomatedFilters(),
            default => throw new InvalidArgumentException(sprintf('Unknown field name "%s".', $fieldName)),
        };
    }

    protected function mapByCategory(string $fieldName, Category $category): mixed
    {
        return match ($fieldName) {
            'uuid' => $category->getUuid(),
            'description' => $category->getDescription($this->domain->getId()),
            'seoH1' => $category->getSeoH1($this->domain->getId()),
            'seoTitle' => $category->getSeoTitle($this->domain->getId()),
            'seoMetaDescription' => $category->getSeoMetaDescription($this->domain->getId()),
            'slug' => $this->categorySlugBatchLoader->load($category->getId()),
            'originalCategorySlug' => null,
            default => $this->mapCommonFields($fieldName, $category),
        };
    }

    protected function mapByReadyCategorySeoMix(string $fieldName, ReadyCategorySeoMix $readyCategorySeoMix): mixed
    {
        $category = $readyCategorySeoMix->getCategory();

        return match ($fieldName) {
            'uuid' => $readyCategorySeoMix->getUuid(),
            'description' => $readyCategorySeoMix->getDescription() ?? '',
            'seoH1' => $readyCategorySeoMix->getH1(),
            'seoTitle' => $readyCategorySeoMix->getTitle() ?? $readyCategorySeoMix->getH1(),
            'seoMetaDescription' => $readyCategorySeoMix->getMetaDescription() ?? $category->getSeoMetaDescription($this->domain->getId()),
            'slug' => $this->categorySeoSlugBatchLoader->load($readyCategorySeoMix->getId()),
            'originalCategorySlug' => $this->categorySlugBatchLoader->load($category->getId()),
            default => $this->mapCommonFields($fieldName, $category),
        };
    }
}
