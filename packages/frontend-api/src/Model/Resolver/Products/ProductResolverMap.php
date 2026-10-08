<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use ArrayObject;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\MethodNotFoundException;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductArrayFieldMapper;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class ProductResolverMap extends ResolverMap
{
    public function __construct(
        protected readonly ProductArrayFieldMapper $productArrayFieldMapper,
    ) {
    }

    #[Override]
    protected function map(): array
    {
        return [
            'Product' => [
                self::RESOLVE_TYPE => function (array $data) {
                    if ($data['is_main_variant']) {
                        return 'MainVariant';
                    }

                    if ($data['main_variant_id'] !== null) {
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
            self::RESOLVE_FIELD => function (array $value, ArgumentInterface $args, ArrayObject $context, ResolveInfo $info) {
                try {
                    return $this->getObjectMethodForField($this->productArrayFieldMapper, $info->fieldName)($value);
                } catch (MethodNotFoundException) {
                    // keys of the Elasticsearch data are usually the names of the fields in snake_case
                    return $value[$info->fieldName] ?? $value[new CamelCaseToSnakeCaseNameConverter()->normalize($info->fieldName)] ?? null;
                }
            },
        ];
    }

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
