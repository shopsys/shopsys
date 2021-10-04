<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Image;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;

class NotificationBarImagesResolver extends AbstractImagesResolver implements AliasedInterface
{
    protected static string $entityName = 'notificationBar';

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return ['resolveByEntity' => 'notificationBarImageResolver'];
    }
}
