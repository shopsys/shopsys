<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Category;

use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use App\Model\Category\Category;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use ArrayObject;
use GraphQL\Type\Definition\ResolveInfo;
use InvalidArgumentException;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoryResolverMap as BaseCategoryResolverMap;

class CategoryResolverMap extends BaseCategoryResolverMap
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Overblog\DataLoader\DataLoaderInterface $readyCategorySeoMixesBatchLoader
     * @param \Overblog\DataLoader\DataLoaderInterface $categoryChildrenBatchLoader
     * @param \Overblog\DataLoader\DataLoaderInterface $linkedCategoriesBatchLoader
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     */
    public function __construct(
        Domain $domain,
        private FriendlyUrlFacade $friendlyUrlFacade,
        private DataLoaderInterface $readyCategorySeoMixesBatchLoader,
        private DataLoaderInterface $categoryChildrenBatchLoader,
        private DataLoaderInterface $linkedCategoriesBatchLoader,
        private CategoryFacade $categoryFacade,
    ) {
        parent::__construct($domain);
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
    private function mapByCategory(string $fieldName, Category $category)
    {
        switch ($fieldName) {
            case 'id':
                return $category->getId();
            case 'uuid':
                return $category->getUuid();
            case 'name':
                return $category->getName($this->domain->getLocale()) ?? '';
            case 'description':
                return $category->getDescription($this->domain->getId());
            case 'children':
                return $this->categoryChildrenBatchLoader->load($category);
            case 'parent':
                $parent = $category->getParent();

                return $parent !== null && $parent->getParent() !== null ? $parent : null;
            case 'seoH1':
                return $category->getSeoH1($this->domain->getId());
            case 'seoTitle':
                return $category->getSeoTitle($this->domain->getId());
            case 'seoMetaDescription':
                return $category->getSeoMetaDescription($this->domain->getId());
            case 'slug':
                return $this->getSlug($category->getId(), 'front_product_list');
            case 'originalCategorySlug':
                return null;
            case 'readyCategorySeoMixLinks':
                return $this->readyCategorySeoMixesBatchLoader->load($category->getId());
            case 'linkedCategories':
                return $this->linkedCategoriesBatchLoader->load($category);
            case 'categoryHierarchy':
                return $this->categoryFacade->getVisibleCategoriesInPathFromRootOnDomain($category, $this->domain->getId());
            default:
                throw new InvalidArgumentException(sprintf('Unknown field name "%s".', $fieldName));
        }
    }

    /**
     * @param string $fieldName
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix $readyCategorySeoMix
     * @return mixed
     */
    private function mapByReadyCategorySeoMix(string $fieldName, ReadyCategorySeoMix $readyCategorySeoMix)
    {
        $category = $readyCategorySeoMix->getCategory();
        switch ($fieldName) {
            case 'id':
                return $category->getId();
            case 'uuid':
                return $readyCategorySeoMix->getUuid();
            case 'name':
                return $category->getName($this->domain->getLocale()) ?? '';
            case 'description':
                return $readyCategorySeoMix->getDescription() ?? '';
            case 'children':
                return $this->categoryChildrenBatchLoader->load($category);
            case 'parent':
                $parent = $category->getParent();

                return $parent !== null && $parent->getParent() !== null ? $parent : null;
            case 'seoH1':
                return $readyCategorySeoMix->getH1();
            case 'seoTitle':
                return $readyCategorySeoMix->getTitle() ?? $readyCategorySeoMix->getH1();
            case 'seoMetaDescription':
                return $readyCategorySeoMix->getMetaDescription() ?? $category->getSeoMetaDescription($this->domain->getId());
            case 'slug':
                return $this->getSlug($readyCategorySeoMix->getId(), 'front_category_seo');
            case 'originalCategorySlug':
                return $this->getSlug($category->getId(), 'front_product_list');
            case 'readyCategorySeoMixLinks':
                return $this->readyCategorySeoMixesBatchLoader->load($category->getId());
            case 'linkedCategories':
                return $this->linkedCategoriesBatchLoader->load($category);
            default:
                throw new InvalidArgumentException(sprintf('Unknown field name "%s".', $fieldName));
        }
    }
}
