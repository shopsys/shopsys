<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Products;

use ArrayObject;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Resolver\FieldResolver;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\MethodNotFoundException;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductResolverMap as BaseProductResolverMap;

class ProductResolverMap extends BaseProductResolverMap
{
    /**
     * @return array<string, callable>
     */
    protected function mapProduct(): array
    {
        return [
            self::RESOLVE_FIELD => function ($value, ArgumentInterface $args, ArrayObject $context, ResolveInfo $info) {
                if ($value instanceof Product) {
                    throw new Error('Product can be resolved only from Elasticsearch. You cannot return Product entity from any resolver.');
                }

                try {
                    return $this->getObjectMethodForField($this->productArrayFieldMapper, $info->fieldName)($value);
                } catch (MethodNotFoundException $exception) {
                    return FieldResolver::valueFromObjectOrArray($value, $info->fieldName);
                }
            },
        ];
    }
}
