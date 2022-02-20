<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Image;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;

class TransportImagesResolver extends AbstractImagesResolver implements AliasedInterface
{
    protected static string $entityName = 'transport';

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return ['resolveByEntity' => 'transportImageResolver'];
    }
}
