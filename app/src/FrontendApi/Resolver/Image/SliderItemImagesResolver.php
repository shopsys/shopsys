<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Image;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;

class SliderItemImagesResolver extends AbstractImagesResolver implements AliasedInterface
{
    protected static string $entityName = 'sliderItem';

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return ['resolveByEntity' => 'sliderItemImageResolver'];
    }
}
