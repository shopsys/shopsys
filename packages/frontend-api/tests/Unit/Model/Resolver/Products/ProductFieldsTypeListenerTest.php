<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\Resolver\Products;

use Doctrine\Common\Collections\ArrayCollection;
use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use GraphQL\Executor\Promise\Adapter\SyncPromiseQueue;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Event\TypeLoadedEvent;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductFieldsTypeListener;

class ProductFieldsTypeListenerTest extends TestCase
{
    private const PRODUCT_ID = 5;
    private const OTHER_PRODUCT_ID = 7;

    private SyncPromiseAdapter $promiseAdapter;

    private DataLoaderInterface|MockObject $productsVisibleByIdsBatchLoader;

    private InterfaceType $productInterface;

    private ObjectType $productType;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->promiseAdapter = new SyncPromiseAdapter();
        $this->productsVisibleByIdsBatchLoader = $this->createMock(DataLoaderInterface::class);
        $this->productInterface = new InterfaceType([
            'name' => 'Product',
            'fields' => [
                'name' => Type::string(),
            ],
        ]);
        $this->productType = new ObjectType([
            'name' => 'RegularProduct',
            'fields' => [
                'name' => Type::string(),
            ],
            'interfaces' => [$this->productInterface],
        ]);
    }

    public function testProductEntityIsReplacedByElasticsearchArray(): void
    {
        $productArray = [
            'id' => self::PRODUCT_ID,
            'name' => 'Product',
        ];
        $this->expectLoaderLoad([self::PRODUCT_ID], [$productArray]);

        $resolvedValue = $this->resolveProductField(fn () => $this->createProduct(self::PRODUCT_ID));

        $this->assertSame($productArray, $resolvedValue);
    }

    public function testProductEntityInFieldOfProductInterfaceIsReplacedByElasticsearchArray(): void
    {
        $productArray = ['id' => self::PRODUCT_ID];
        $this->expectLoaderLoad([self::PRODUCT_ID], [$productArray]);

        $resolvedValue = $this->resolveProductField(
            fn () => $this->createProduct(self::PRODUCT_ID),
            Type::nonNull($this->productInterface),
        );

        $this->assertSame($productArray, $resolvedValue);
    }

    public function testProductEntityNotVisibleForCurrentCustomerIsResolvedAsNull(): void
    {
        $this->expectLoaderLoad([self::PRODUCT_ID], []);

        $resolvedValue = $this->resolveProductField(fn () => $this->createProduct(self::PRODUCT_ID));

        $this->assertNull($resolvedValue);
    }

    /**
     * @param callable(\Shopsys\FrameworkBundle\Model\Product\Product, \Shopsys\FrameworkBundle\Model\Product\Product): iterable<\Shopsys\FrameworkBundle\Model\Product\Product|null> $createList
     */
    #[DataProvider('listsOfProductEntitiesProvider')]
    public function testListOfProductEntitiesIsLoadedAtOnce(callable $createList): void
    {
        $productArrays = [['id' => self::PRODUCT_ID], ['id' => self::OTHER_PRODUCT_ID]];
        $this->expectLoaderLoad([self::PRODUCT_ID, self::OTHER_PRODUCT_ID], $productArrays);

        $resolvedValue = $this->resolveProductField(
            fn () => $createList($this->createProduct(self::PRODUCT_ID), $this->createProduct(self::OTHER_PRODUCT_ID)),
            Type::nonNull(Type::listOf(Type::nonNull($this->productType))),
        );

        $this->assertSame($productArrays, $resolvedValue);
    }

    /**
     * @return iterable<string, array{callable}>
     */
    public static function listsOfProductEntitiesProvider(): iterable
    {
        yield 'list' => [static fn (Product $product, Product $otherProduct) => [$product, $otherProduct]];

        yield 'list with keys that are not sequential' => [
            static fn (Product $product, Product $otherProduct) => [1 => $product, 3 => $otherProduct],
        ];

        yield 'collection' => [
            static fn (Product $product, Product $otherProduct) => new ArrayCollection([$product, $otherProduct]),
        ];

        yield 'list starting with null' => [
            static fn (Product $product, Product $otherProduct) => [null, $product, $otherProduct],
        ];
    }

    public function testListOfProductEntitiesResolvedByPromiseIsLoadedAtOnce(): void
    {
        $productArrays = [['id' => self::PRODUCT_ID], ['id' => self::OTHER_PRODUCT_ID]];
        $this->expectLoaderLoad([self::PRODUCT_ID, self::OTHER_PRODUCT_ID], $productArrays);

        $resolvedValue = $this->resolveProductField(
            fn () => $this->promiseAdapter->createFulfilled(
                [$this->createProduct(self::PRODUCT_ID), $this->createProduct(self::OTHER_PRODUCT_ID)],
            ),
            Type::listOf($this->productType),
        );

        $this->assertSame($productArrays, $resolvedValue);
    }

    public function testProductEntityResolvedByPromiseIsReplacedByElasticsearchArray(): void
    {
        $productArray = ['id' => self::PRODUCT_ID];
        $this->expectLoaderLoad([self::PRODUCT_ID], [$productArray]);

        $resolvedValue = $this->resolveProductField(
            fn () => $this->promiseAdapter->createFulfilled($this->createProduct(self::PRODUCT_ID)),
        );

        $this->assertSame($productArray, $resolvedValue);
    }

    #[DataProvider('valuesWithoutProductEntityProvider')]
    public function testValueWithoutProductEntityIsNotChanged(mixed $value): void
    {
        $this->productsVisibleByIdsBatchLoader->expects($this->never())->method('load');

        $resolvedValue = $this->resolveProductField(static fn () => $value);

        $this->assertSame($value, $resolvedValue);
    }

    /**
     * @return iterable<string, array{mixed}>
     */
    public static function valuesWithoutProductEntityProvider(): iterable
    {
        yield 'null' => [null];

        yield 'elasticsearch array' => [['id' => self::PRODUCT_ID, 'name' => 'Product']];

        yield 'list of elasticsearch arrays' => [[['id' => self::PRODUCT_ID], ['id' => self::OTHER_PRODUCT_ID]]];

        yield 'empty list' => [[]];
    }

    public function testFieldOfOtherTypeIsNotDecorated(): void
    {
        $this->productsVisibleByIdsBatchLoader->expects($this->never())->method('load');

        $resolver = static fn () => 'name';
        $type = $this->createDecoratedType(['name' => ['type' => Type::string(), 'resolve' => $resolver]]);

        $this->assertSame($resolver, $type->getField('name')->resolveFn);
    }

    public function testFieldWithTypeDefinedByCallableIsDecorated(): void
    {
        $productArray = ['id' => self::PRODUCT_ID];
        $this->expectLoaderLoad([self::PRODUCT_ID], [$productArray]);

        $type = $this->createDecoratedType([
            'product' => [
                'type' => fn () => $this->productType,
                'resolve' => fn () => $this->createProduct(self::PRODUCT_ID),
            ],
        ]);

        $this->assertSame($productArray, $this->resolve($type->getField('product')->resolveFn));
    }

    public function testFieldDefinedJustByTypeIsDecorated(): void
    {
        $productArray = ['id' => self::PRODUCT_ID];
        $this->expectLoaderLoad([self::PRODUCT_ID], [$productArray]);

        $type = $this->createDecoratedType(
            ['product' => $this->productType],
            typeFieldResolver: fn () => $this->createProduct(self::PRODUCT_ID),
        );

        $this->assertSame($productArray, $this->resolve($type->getField('product')->resolveFn));
    }

    public function testFieldWithoutOwnResolverUsesResolverOfType(): void
    {
        $productArray = ['id' => self::PRODUCT_ID];
        $this->expectLoaderLoad([self::PRODUCT_ID], [$productArray]);

        $type = $this->createDecoratedType(
            ['product' => ['type' => $this->productType]],
            typeFieldResolver: fn () => $this->createProduct(self::PRODUCT_ID),
        );

        $this->assertSame($productArray, $this->resolve($type->getField('product')->resolveFn));
    }

    public function testFieldWithoutOwnResolverUsesDefaultResolverWhenTypeHasNone(): void
    {
        $productArray = ['id' => self::PRODUCT_ID];
        $this->expectLoaderLoad([self::PRODUCT_ID], [$productArray]);

        $type = $this->createDecoratedType(
            ['product' => ['type' => $this->productType]],
            defaultFieldResolver: fn () => $this->createProduct(self::PRODUCT_ID),
        );

        $this->assertSame($productArray, $this->resolve($type->getField('product')->resolveFn));
    }

    /**
     * @param int[] $expectedProductIds
     * @param array[] $productArrays
     */
    private function expectLoaderLoad(array $expectedProductIds, array $productArrays): void
    {
        $this->productsVisibleByIdsBatchLoader->expects($this->once())
            ->method('load')
            ->with($expectedProductIds)
            ->willReturn($this->promiseAdapter->createFulfilled($productArrays));
    }

    private function createProduct(int $productId): Product
    {
        $product = $this->createStub(Product::class);
        $product->method('getId')->willReturn($productId);

        return $product;
    }

    private function resolveProductField(callable $resolver, ?Type $fieldType = null): mixed
    {
        $type = $this->createDecoratedType([
            'product' => ['type' => $fieldType ?? $this->productType, 'resolve' => $resolver],
        ]);

        return $this->resolve($type->getField('product')->resolveFn);
    }

    /**
     * @param array<string, mixed> $fields
     */
    private function createDecoratedType(
        array $fields,
        ?callable $typeFieldResolver = null,
        ?callable $defaultFieldResolver = null,
    ): ObjectType {
        $type = new ObjectType([
            'name' => 'OrderItem',
            'fields' => static fn () => $fields,
            'resolveField' => $typeFieldResolver,
        ]);

        $listener = new ProductFieldsTypeListener(
            $this->productsVisibleByIdsBatchLoader,
            $defaultFieldResolver ?? static fn () => null,
        );
        $listener->onTypeLoaded(new TypeLoadedEvent($type, 'default'));

        return $type;
    }

    private function resolve(callable $resolver): mixed
    {
        $value = $resolver(null, [], null, null);

        if (!$value instanceof Promise) {
            return $value;
        }

        $resolvedValue = null;
        $value->then(static function ($promiseValue) use (&$resolvedValue): void {
            $resolvedValue = $promiseValue;
        });
        SyncPromiseQueue::run();

        return $resolvedValue;
    }
}
