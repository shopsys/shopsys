<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Category;

use App\Model\Category\Category;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use ArrayObject;
use GraphQL\Type\Definition\ResolveInfo;
use InvalidArgumentException;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoryResolverMap as BaseCategoryResolverMap;

class CategoryResolverMap extends BaseCategoryResolverMap
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade $hreflangLinksFacade
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Overblog\DataLoader\DataLoaderInterface $readyCategorySeoMixesBatchLoader
     * @param \Overblog\DataLoader\DataLoaderInterface $categoryChildrenBatchLoader
     * @param \Overblog\DataLoader\DataLoaderInterface $linkedCategoriesBatchLoader
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     */
    public function __construct(
        Domain $domain,
        HreflangLinksFacade $hreflangLinksFacade,
        private readonly FriendlyUrlFacade $friendlyUrlFacade,
        private readonly DataLoaderInterface $readyCategorySeoMixesBatchLoader,
        private readonly DataLoaderInterface $categoryChildrenBatchLoader,
        private readonly DataLoaderInterface $linkedCategoriesBatchLoader,
        private readonly CategoryFacade $categoryFacade,
    ) {
        parent::__construct($domain, $hreflangLinksFacade);
    }

    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'Category' => [
                self::RESOLVE_FIELD => function ($value, ArgumentInterface $args, ArrayObject $context, ResolveInfo $info) {
                    if ($value instanceof Category) {
                        return $this->mapByCategory($info->fieldName, $value);
                    }

                    if ($value instanceof ReadyCategorySeoMix) {
                        return $this->mapByReadyCategorySeoMix($info->fieldName, $value);
                    }

                    throw new InvalidArgumentException(
                        sprintf(
                            'The "$value" argument must be an instance of "%s" or "%s".',
                            Category::class,
                            ReadyCategorySeoMix::class,
                        ),
                    );
                },
            ],
        ];
    }

    /**
     * @param int $entityId
     * @param string $routeName
     * @return string
     */
    private function getSlug(int $entityId, string $routeName): string
    {
        $friendlyUrlSlug = $this->friendlyUrlFacade->getMainFriendlyUrlSlug(
            $this->domain->getId(),
            $routeName,
            $entityId,
        );

        return '/' . $friendlyUrlSlug;
    }

    /**
     * @param string $fieldName
     * @param \App\Model\Category\Category $category
     * @return mixed
     */
    private function mapCommonFields(string $fieldName, Category $category): mixed
    {
        return match ($fieldName) {
            'id' => $category->getId(),
            'name' => $category->getName($this->domain->getLocale()) ?? '',
            'children' => $this->categoryChildrenBatchLoader->load($category),
            'parent' => $category->getParent() !== null && $category->getParent()->getParent() !== null ? $category->getParent() : null,
            'readyCategorySeoMixLinks' => $this->readyCategorySeoMixesBatchLoader->load($category->getId()),
            'linkedCategories' => $this->linkedCategoriesBatchLoader->load($category),
            'categoryHierarchy' => $this->categoryFacade->getVisibleCategoriesInPathFromRootOnDomain($category, $this->domain->getId()),
            'hreflangLinks' => $this->hreflangLinksFacade->getForCategory($category, $this->domain->getId()),
            default => throw new InvalidArgumentException(sprintf('Unknown field name "%s".', $fieldName)),
        };
    }

    /**
     * @param string $fieldName
     * @param \App\Model\Category\Category $category
     * @return mixed
     */
    private function mapByCategory(string $fieldName, Category $category): mixed
    {
        return match ($fieldName) {
            'uuid' => $category->getUuid(),
            'description' => $category->getDescription($this->domain->getId()),
            'seoH1' => $category->getSeoH1($this->domain->getId()),
            'seoTitle' => $category->getSeoTitle($this->domain->getId()),
            'seoMetaDescription' => $category->getSeoMetaDescription($this->domain->getId()),
            'slug' => $this->getSlug($category->getId(), 'front_product_list'),
            'originalCategorySlug' => null,
            default => $this->mapCommonFields($fieldName, $category),
        };
    }

    /**
     * @param string $fieldName
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix $readyCategorySeoMix
     * @return mixed
     */
    private function mapByReadyCategorySeoMix(string $fieldName, ReadyCategorySeoMix $readyCategorySeoMix): mixed
    {
        $category = $readyCategorySeoMix->getCategory();

        return match ($fieldName) {
            'uuid' => $readyCategorySeoMix->getUuid(),
            'description' => $readyCategorySeoMix->getDescription() ?? '',
            'seoH1' => $readyCategorySeoMix->getH1(),
            'seoTitle' => $readyCategorySeoMix->getTitle() ?? $readyCategorySeoMix->getH1(),
            'seoMetaDescription' => $readyCategorySeoMix->getMetaDescription() ?? $category->getSeoMetaDescription($this->domain->getId()),
            'slug' => $this->getSlug($readyCategorySeoMix->getId(), 'front_category_seo'),
            'originalCategorySlug' => $this->getSlug($category->getId(), 'front_product_list'),
            default => $this->mapCommonFields($fieldName, $category),
        };
    }
}
