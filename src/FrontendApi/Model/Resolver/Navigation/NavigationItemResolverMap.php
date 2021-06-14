<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Navigation;

use App\Model\HorizontalMenu\HorizontalMenuItemDetail;
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
                'name' => static function (HorizontalMenuItemDetail $horizontalMenuItemDetail) {
                    return $horizontalMenuItemDetail->getHorizontalMenuItem()->getName();
                },
                'link' => static function (HorizontalMenuItemDetail $horizontalMenuItemDetail) {
                    return $horizontalMenuItemDetail->getHorizontalMenuItem()->getUrl();
                },
                'categoriesByColumns' => static function (HorizontalMenuItemDetail $horizontalMenuItemDetail) {
                    foreach ($horizontalMenuItemDetail->getCategoryDetailsByColumnNumber() as $columnNumber => $categories) {
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
