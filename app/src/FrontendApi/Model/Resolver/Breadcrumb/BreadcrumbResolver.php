<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Breadcrumb;

use App\Component\Breadcrumb\BreadcrumbFacade;
use App\Model\Category\Category;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use InvalidArgumentException;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class BreadcrumbResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \App\Component\Breadcrumb\BreadcrumbFacade
     */
    private BreadcrumbFacade $breadcrumbFacade;

    /**
     * @param \App\Component\Breadcrumb\BreadcrumbFacade $breadcrumbFacade
     */
    public function __construct(
        BreadcrumbFacade $breadcrumbFacade
    ) {
        $this->breadcrumbFacade = $breadcrumbFacade;
    }

    /**
     * @param int $id
     * @param string $routeName
     * @return array<int, array{name: string, slug: string}>
     */
    public function resolveBreadcrumb(int $id, string $routeName): array
    {
        return $this->breadcrumbFacade->getBreadcrumbOnCurrentDomain(
            $id,
            $routeName
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
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return [
            'resolveBreadcrumb' => 'resolveBreadcrumb',
            'resolveCategoryBreadcrumb' => 'resolveCategoryBreadcrumb',
        ];
    }
}
