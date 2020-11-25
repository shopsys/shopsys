<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider;

class ProductByUuidResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider
     */
    protected ProductElasticsearchProvider $productElasticsearchProvider;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider $productElasticsearchProvider
     */
    public function __construct(
        ProductElasticsearchProvider $productElasticsearchProvider
    ) {
        $this->productElasticsearchProvider = $productElasticsearchProvider;
    }

    /**
     * @param string $uuid
     * @return array
     */
    public function resolver(string $uuid): array
    {
        try {
            return $this->productElasticsearchProvider->getVisibleProductArrayByUuid($uuid);
        } catch (ProductNotFoundException $productNotFoundException) {
            throw new UserError($productNotFoundException->getMessage());
        }
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'resolver' => 'productByUuid',
        ];
    }
}
