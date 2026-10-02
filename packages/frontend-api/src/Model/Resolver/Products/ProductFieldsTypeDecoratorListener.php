<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Event\TypeLoadedEvent;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Traversable;

/**
 * Product types are always resolved from Elasticsearch data, so a product entity returned by a resolver
 * of any field of a product type (e.g. OrderItem.product) is replaced by its Elasticsearch array
 */
class ProductFieldsTypeDecoratorListener
{
    /**
     * @var string[]
     */
    protected array $productTypeNames = ['Product', 'RegularProduct', 'Variant', 'MainVariant'];

    /**
     * @param callable $defaultFieldResolver
     */
    public function __construct(
        protected readonly DataLoaderInterface $productsVisibleByIdsBatchLoader,
        protected readonly mixed $defaultFieldResolver,
    ) {
    }

    public function onTypeLoaded(TypeLoadedEvent $event): void
    {
        $type = $event->getType();

        if (!$type instanceof ObjectType) {
            return;
        }

        $fields = $type->config['fields'];

        $decoratedFields = function () use ($type, $fields): array {
            $fields = is_callable($fields) ? $fields() : $fields;
            $fields = $fields instanceof Traversable ? iterator_to_array($fields) : (array)$fields;

            foreach ($fields as &$field) {
                if (!$this->isProductField($field)) {
                    continue;
                }

                $originalResolver = $field['resolve'] ?? null;
                // the same resolver lookup as GraphQL\Executor\ReferenceExecutor::resolveField() does
                $field['resolve'] = fn (...$arguments) => $this->normalizeResolvedValue(
                    ($originalResolver ?? $type->resolveFieldFn ?? $this->defaultFieldResolver)(...$arguments),
                );
            }

            return $fields;
        };

        $type->config['fields'] = is_callable($fields) ? $decoratedFields : $decoratedFields();
    }

    protected function isProductField(mixed $field): bool
    {
        if (!is_array($field) || !isset($field['type'])) {
            return false;
        }

        $fieldType = is_callable($field['type']) ? $field['type']() : $field['type'];

        return in_array(Type::getNamedType($fieldType)?->name, $this->productTypeNames, true);
    }

    /**
     * Products not visible for the current customer become null (single value) or are omitted (list)
     */
    protected function normalizeResolvedValue(mixed $value): mixed
    {
        if ($value instanceof Promise) {
            return $value->then(fn ($resolvedValue) => $this->normalizeResolvedValue($resolvedValue));
        }

        // only the ID is read, so a lazy loaded product entity is not initialized
        if ($value instanceof Product) {
            return $this->productsVisibleByIdsBatchLoader->load([$value->getId()])
                ->then(static fn (array $products) => $products[0] ?? null);
        }

        if (is_array($value) && array_is_list($value) && ($value[0] ?? null) instanceof Product) {
            // the loader keeps the order of the IDs and omits products that are not visible
            return $this->productsVisibleByIdsBatchLoader->load(
                array_map(static fn (Product $product) => $product->getId(), $value),
            );
        }

        return $value;
    }
}
