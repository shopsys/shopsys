<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Breadcrumb;

use App\Model\Category\Category;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use App\Model\Product\Product;
use InvalidArgumentException;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbResolver as BackendBreadcrumbResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BreadcrumbResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbResolver
     */
    protected BackendBreadcrumbResolver $breadcrumbResolver;

    /**
     * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     */
    protected UrlGeneratorInterface $urlGenerator;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbResolver $breadcrumbResolver
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $urlGenerator
     */
    public function __construct(
        BackendBreadcrumbResolver $breadcrumbResolver,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->breadcrumbResolver = $breadcrumbResolver;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param int $id
     * @param string $routeName
     * @return array<int, array{name: string, slug: string}>
     */
    public function resolveBreadcrumb(int $id, string $routeName): array
    {
        $breadcrumbItems = $this->breadcrumbResolver->resolveBreadcrumbItems(
            $routeName,
            ['id' => $id]
        );

        return array_map(
            function (BreadcrumbItem $breadcrumbItem) use ($id, $routeName) {
                $breadcrumbItemRouteName = $breadcrumbItem->getRouteName() ?? $routeName;
                $breadcrumbItemRouteParams = $breadcrumbItem->getRouteName() ? $breadcrumbItem->getRouteParameters() : ['id' => $id];

                return [
                    'name' => $breadcrumbItem->getName(),
                    'slug' => $this->urlGenerator->generate($breadcrumbItemRouteName, $breadcrumbItemRouteParams),
                ];
            },
            $breadcrumbItems
        );
    }

    /**
     * @param \App\Model\Category\Category|\App\Model\CategorySeo\ReadyCategorySeoMix $categoryOrReadyCategorySeoMix
     * @return array[]
     */
    public function resolveCategoryBreadcrumb($categoryOrReadyCategorySeoMix): array
    {
        if ($categoryOrReadyCategorySeoMix instanceof Category) {
            $categoryId = $categoryOrReadyCategorySeoMix->getId();
        } elseif ($categoryOrReadyCategorySeoMix instanceof ReadyCategorySeoMix) {
            $categoryId = $categoryOrReadyCategorySeoMix->getCategory()->getId();
        } else {
            throw new InvalidArgumentException(
                sprintf(
                    'The "$categoryOrReadyCategorySeoMix" argument must be an instance of "%s" or "%s".',
                    Category::class,
                    ReadyCategorySeoMix::class
                ),
            );
        }

        return $this->resolveBreadcrumb($categoryId, 'front_product_list');
    }

    /**
     * @param \App\Model\Product\Product|array $product
     * @return array[]
     */
    public function resolveProductBreadcrumb($product): array
    {
        $productId = $product instanceof Product ? $product->getId() : $product['id'];

        return $this->resolveBreadcrumb($productId, 'front_product_detail');
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return [
            'resolveBreadcrumb' => 'resolveBreadcrumb',
            'resolveCategoryBreadcrumb' => 'resolveCategoryBreadcrumb',
            'resolveProductBreadcrumb' => 'resolveProductBreadcrumb',
        ];
    }
}
