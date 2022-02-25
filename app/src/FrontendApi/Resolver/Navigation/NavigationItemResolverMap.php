<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Navigation;

use App\Model\Navigation\NavigationItemDetail;
use Overblog\GraphQLBundle\Resolver\ResolverMap;

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
                    foreach ($navigationItemDetail->getCategoryDetailsByColumnNumber() as $columnNumber => $categories) {
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
