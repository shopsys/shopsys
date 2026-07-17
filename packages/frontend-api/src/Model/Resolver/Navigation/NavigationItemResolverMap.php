<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Navigation;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Model\Navigation\NavigationItemDetail;
use Shopsys\FrameworkBundle\Model\Navigation\NavigationItemTypeEnum;

class NavigationItemResolverMap extends ResolverMap
{
    #[Override]
    protected function map(): array
    {
        return [
            'NavigationItem' => [
                'name' => static function (NavigationItemDetail $navigationItemDetail) {
                    return $navigationItemDetail->getNavigationItem()->getName();
                },
                'type' => static function (NavigationItemDetail $navigationItemDetail) {
                    return $navigationItemDetail->getNavigationItem()->getType();
                },
                'link' => static function (NavigationItemDetail $navigationItemDetail) {
                    if ($navigationItemDetail->getNavigationItem()->getType() === NavigationItemTypeEnum::CATEGORIES) {
                        return null;
                    }

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
