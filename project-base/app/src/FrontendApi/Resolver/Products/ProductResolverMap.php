<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Products;

use ArrayObject;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Resolver\FieldResolver;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\MethodNotFoundException;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductResolverMap as BaseProductResolverMap;

/**
 * @property \App\Model\Product\Flag\FlagFacade $flagFacade
 * @property \App\Model\Category\CategoryFacade $categoryFacade
 * @property \App\FrontendApi\Resolver\Products\DataMapper\ProductArrayFieldMapper|null $productArrayFieldMapper
 * @method __construct(\Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade $productCollectionFacade, \App\Model\Product\Flag\FlagFacade $flagFacade, \App\Model\Category\CategoryFacade $categoryFacade, \App\FrontendApi\Resolver\Products\DataMapper\ProductEntityFieldMapper|null $productEntityFieldMapper = null, \App\FrontendApi\Resolver\Products\DataMapper\ProductArrayFieldMapper|null $productArrayFieldMapper = null)
 * @method setProductArrayFieldMapper(\App\FrontendApi\Resolver\Products\DataMapper\ProductArrayFieldMapper $productArrayFieldMapper)
 * @method \App\Model\Product\Flag\Flag[] getFlagsForData(\App\Model\Product\Product|array $data)
 * @method \App\Model\Category\Category[] getCategoriesForData(array $data)
 * @property \App\FrontendApi\Resolver\Products\DataMapper\ProductEntityFieldMapper|null $productEntityFieldMapper
 * @method setProductEntityFieldMapper(\App\FrontendApi\Resolver\Products\DataMapper\ProductEntityFieldMapper $productEntityFieldMapper)
 */
class ProductResolverMap extends BaseProductResolverMap
{
    /**
     * @return array<string, callable>
     */
    protected function mapProduct(): array
    {
        return [
            self::RESOLVE_FIELD => function ($value, ArgumentInterface $args, ArrayObject $context, ResolveInfo $info) {
                /** @var \App\FrontendApi\Resolver\Products\DataMapper\ProductArrayFieldMapper|\App\FrontendApi\Resolver\Products\DataMapper\ProductEntityFieldMapper $mapper */
                $mapper = $value instanceof Product ? $this->productEntityFieldMapper : $this->productArrayFieldMapper;

                try {
                    return $this->getObjectMethodForField($mapper, $info->fieldName)($value);
                } catch (MethodNotFoundException $exception) {
                    return FieldResolver::valueFromObjectOrArray($value, $info->fieldName);
                }
            },
            'availability' => function ($value) {
                /** @var \App\FrontendApi\Resolver\Products\DataMapper\ProductArrayFieldMapper|\App\FrontendApi\Resolver\Products\DataMapper\ProductEntityFieldMapper $mapper */
                $mapper = $value instanceof Product ? $this->productEntityFieldMapper : $this->productArrayFieldMapper;

                return $mapper->getExtendedAvailability($value);
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
            $methodPromiseCandidate = $methodCandidate . 'Promise';
            if (method_exists($mapper, $methodPromiseCandidate)) {
                return [$mapper, $methodPromiseCandidate];
            }
            if (method_exists($mapper, $methodCandidate)) {
                return [$mapper, $methodCandidate];
            }
        }

        throw new MethodNotFoundException($fieldName, $mapper);
    }
}
