<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use ArrayObject;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Resolver\FieldResolver;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\MethodNotFoundException;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductArrayFieldMapper;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductEntityFieldMapper;

class ProductResolverMap extends ResolverMap
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductEntityFieldMapper $productEntityFieldMapper
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductArrayFieldMapper $productArrayFieldMapper
     */
    public function __construct(
        protected readonly ProductEntityFieldMapper $productEntityFieldMapper,
        protected readonly ProductArrayFieldMapper $productArrayFieldMapper,
    ) {
    }

    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'Product' => [
                self::RESOLVE_TYPE => function ($data) {
                    $isMainVariant = $data instanceof Product ? $data->isMainVariant() : $data['is_main_variant'];
                    $isVariant = $data instanceof Product ? $data->isVariant() : $data['main_variant_id'] !== null;

                    if ($isMainVariant) {
                        return 'MainVariant';
                    }

                    if ($isVariant) {
                        return 'Variant';
                    }

                    return 'RegularProduct';
                },
            ],
            'RegularProduct' => $this->mapProduct(),
            'Variant' => $this->mapProduct(),
            'MainVariant' => $this->mapProduct(),
        ];
    }

    /**
     * @return callable[]
     */
    protected function mapProduct(): array
    {
        return [
            self::RESOLVE_FIELD => function ($value, ArgumentInterface $args, ArrayObject $context, ResolveInfo $info) {
                $mapper = $value instanceof Product ? $this->productEntityFieldMapper : $this->productArrayFieldMapper;

                try {
                    return $this->getObjectMethodForField($mapper, $info->fieldName)($value);
                } catch (MethodNotFoundException $exception) {
                    return FieldResolver::valueFromObjectOrArray($value, $info->fieldName);
                }
            },
        ];
    }

    /**
     * @param object $mapper
     * @param string $fieldName
     * @return callable
     */
    protected function getObjectMethodForField(object $mapper, string $fieldName): callable
    {
        $prefixes = ['get', 'is', ''];

        foreach ($prefixes as $prefix) {
            $methodCandidate = lcfirst($prefix . ucfirst($fieldName));
            if (method_exists($mapper, $methodCandidate)) {
                return [$mapper, $methodCandidate];
            }
        }

        throw new MethodNotFoundException($fieldName, $mapper);
    }
}
