<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Navigation;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Model\Navigation\NavigationItemDetail;

class NavigationItemResolverMap extends ResolverMap
{
    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'NavigationItem' => [
                'name' => static function (NavigationItemDetail $navigationItemDetail) {
                    return $navigationItemDetail->getNavigationItem()->getName();
                },
                'link' => static function (NavigationItemDetail $navigationItemDetail) {
                    return $navigationItemDetail->getNavigationItem()->getUrl();
                },
                'categoriesByColumns' => static function (NavigationItemDetail $navigationItemDetail) {
                    foreach ($navigationItemDetail->getCategoriesByColumnNumber() as $columnNumber => $categories) {
                        yield [
                            'columnNumber' => $columnNumber,
                            'categories' => $categories,
                        ];
                    }
                },
            ],
        ];
    }
}
