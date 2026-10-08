<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use Doctrine\Common\Collections\Collection;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Type\Definition\ImplementingType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Event\Events;
use Overblog\GraphQLBundle\Event\TypeLoadedEvent;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Traversable;

/**
 * Product types are always resolved from Elasticsearch data. Fields of a product type that return product entities
 * (e.g. OrderItem.product resolved by the default resolver as $orderItem->getProduct()) would need their own resolvers
 * to load the product from Elasticsearch, so instead their resolvers are wrapped here and the returned entities
 * are replaced by Elasticsearch arrays loaded in batches by their IDs.
 */
class ProductFieldsTypeListener
{
    protected const string PRODUCT_INTERFACE_NAME = 'Product';

    /**
     * @param callable $defaultFieldResolver
     */
    public function __construct(
        protected readonly DataLoaderInterface $productsVisibleByIdsBatchLoader,
        protected readonly mixed $defaultFieldResolver,
    ) {
    }

    // must run after Overblog\GraphQLBundle\EventListener\TypeDecoratorListener (priority 0) has applied the resolver maps
    #[AsEventListener(event: Events::TYPE_LOADED, priority: -10)]
    public function onTypeLoaded(TypeLoadedEvent $event): void
    {
        $type = $event->getType();

        if (!$type instanceof ObjectType) {
            return;
        }

        $fields = $type->config['fields'];

        // fields are decorated lazily when the schema needs them, so loading of the types of the fields does not happen while this type is being loaded
        $type->config['fields'] = fn () => $this->decorateProductFields(is_callable($fields) ? $fields() : $fields, $type);
    }

    /**
     * @param iterable<string, mixed> $fields
     * @return array<string, mixed>
     */
    protected function decorateProductFields(iterable $fields, ObjectType $type): array
    {
        $fields = $fields instanceof Traversable ? iterator_to_array($fields) : $fields;

        foreach ($fields as &$field) {
            // a field can be defined just by its type
            $field = $field instanceof Type ? ['type' => $field] : $field;

            if (!$this->isProductField($field)) {
                continue;
            }

            $originalResolver = $this->getOriginalResolver($field, $type);
            $field['resolve'] = fn (...$arguments) => $this->normalizeResolvedValue($originalResolver(...$arguments));
        }

        return $fields;
    }

    /**
     * Uses the same lookup as GraphQL\Executor\ReferenceExecutor::resolveField(),
     * i.e. the resolver of the field, then the resolver of the type, and the default resolver as the last option
     *
     * @param array<string, mixed> $field
     */
    protected function getOriginalResolver(array $field, ObjectType $type): callable
    {
        return $field['resolve'] ?? $type->resolveFieldFn ?? $this->defaultFieldResolver;
    }

    protected function isProductField(mixed $field): bool
    {
        if (!is_array($field) || !isset($field['type'])) {
            return false;
        }

        // the type of the field can be defined by a callable as well
        $fieldType = $field['type'] instanceof Type || !is_callable($field['type']) ? $field['type'] : $field['type']();

        if (!$fieldType instanceof Type) {
            return false;
        }

        $namedFieldType = Type::getNamedType($fieldType);
        $interfaces = $namedFieldType instanceof ImplementingType ? $namedFieldType->getInterfaces() : [];
        $typeNames = [
            $namedFieldType->name(),
            ...array_map(static fn (InterfaceType $interface) => $interface->name(), $interfaces),
        ];

        // the field is either of the product interface itself or of a type that implements it
        return in_array(static::PRODUCT_INTERFACE_NAME, $typeNames, true);
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

        if ($value instanceof Collection) {
            $value = $value->toArray();
        }

        if (!is_array($value)) {
            return $value;
        }

        // keys of the list do not have to be sequential (e.g. a filtered list)
        $products = array_filter($value, static fn ($item) => $item instanceof Product);

        if ($products !== []) {
            // the loader keeps the order of the IDs and omits products that are not visible
            return $this->productsVisibleByIdsBatchLoader->load(
                array_map(static fn (Product $product) => $product->getId(), array_values($products)),
            );
        }

        return $value;
    }
}
