<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Image;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductImagesResolver extends AbstractImagesResolver implements AliasedInterface
{
    /**
     * @param \App\Model\Product\Product|array $data
     * @param string|null $type
     * @param array|null $sizes
     * @return array
     */
    public function resolveByProduct($data, ?string $type, ?array $sizes): array
    {
        $productId = $data instanceof Product ? $data->getId() : $data['id'];
        return $this->resolveByEntityId($productId, 'product', $type, $sizes);
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return ['resolveByProduct' => 'productImageResolver'];
    }
}
