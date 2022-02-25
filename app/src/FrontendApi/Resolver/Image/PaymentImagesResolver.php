<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Image;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;

class PaymentImagesResolver extends AbstractImagesResolver implements AliasedInterface
{
    protected static string $entityName = 'payment';

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return ['resolveByEntity' => 'paymentImageResolver'];
    }
}
