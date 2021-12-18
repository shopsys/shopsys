<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Category;

use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use App\Model\Category\Category;
use App\Model\Category\CategoryFacade;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use ArrayObject;
use GraphQL\Type\Definition\ResolveInfo;
use InvalidArgumentException;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoryResolverMap as BaseCategoryResolverMap;

class CategoryResolverMap extends BaseCategoryResolverMap
{
    /**
     * @var \App\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @var \App\Model\Category\CategoryFacade
     */
    private CategoryFacade $categoryFacade;

    /**
     * @var \Overblog\DataLoader\DataLoaderInterface
     */
    private DataLoaderInterface $readyCategorySeoMixesBatchLoader;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \Overblog\DataLoader\DataLoaderInterface $readyCategorySeoMixesBatchLoader
     */
    public function __construct(
        Domain $domain,
        FriendlyUrlFacade $friendlyUrlFacade,
        CategoryFacade $categoryFacade,
        DataLoaderInterface $readyCategorySeoMixesBatchLoader
    ) {
        parent::__construct($domain);

        $this->friendlyUrlFacade = $friendlyUrlFacade;
        $this->categoryFacade = $categoryFacade;
        $this->readyCategorySeoMixesBatchLoader = $readyCategorySeoMixesBatchLoader;
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
                            ReadyCategorySeoMix::class
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
        $friendlyUrl = $this->friendlyUrlFacade->getMainFriendlyUrl(
            $this->domain->getId(),
            $routeName,
            $entityId
        );

        return '/' . $friendlyUrl->getSlug();
    }

    /**
     * @param string $fieldName
     * @param \App\Model\Category\Category $category
     * @return mixed
     */
    private function mapByCategory(string $fieldName, Category $category)
    {
        switch ($fieldName) {
            case 'uuid':
                return $category->getUuid();
            case 'name':
                return $category->getName($this->domain->getLocale()) ?? '';
            case 'children':
                return $this->categoryFacade->getAllVisibleChildrenByCategoryAndDomainConfig($category, $this->domain->getCurrentDomainConfig());
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
            case 'readyCategorySeoMixLinks':
                return $this->readyCategorySeoMixesBatchLoader->load($category->getId());
            case 'linkedCategories':
                return $this->categoryFacade->getVisibleLinkedCategories($category, $this->domain->getId());
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
            case 'uuid':
                return $category->getUuid();
            case 'name':
                return $category->getName($this->domain->getLocale()) ?? '';
            case 'children':
                return $this->categoryFacade->getAllVisibleChildrenByCategoryAndDomainId($category, $this->domain->getId());
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
            case 'readyCategorySeoMixLinks':
                return $this->readyCategorySeoMixesBatchLoader->load($category->getId());
            case 'linkedCategories':
                return $this->categoryFacade->getVisibleLinkedCategories($category, $this->domain->getId());
            default:
                throw new InvalidArgumentException(sprintf('Unknown field name "%s".', $fieldName));
        }
    }
}
