<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Image;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;

class BrandImagesResolver extends AbstractImagesResolver implements AliasedInterface
{
    protected static string $entityName = 'brand';

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return ['resolveByEntity' => 'brandImageResolver'];
    }
}
