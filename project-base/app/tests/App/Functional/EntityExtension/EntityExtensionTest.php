<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension;

use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\SchemaValidator;
use Shopsys\FrameworkBundle\Component\Doctrine\DatabaseSchemaFacade;
use Tests\App\Functional\EntityExtension\Model\Category\Category;
use Tests\App\Functional\EntityExtension\Model\ExtendedCategory\CategoryManyToManyBidirectionalEntity;
use Tests\App\Functional\EntityExtension\Model\ExtendedCategory\CategoryOneToManyBidirectionalEntity;
use Tests\App\Functional\EntityExtension\Model\ExtendedCategory\CategoryOneToOneBidirectionalEntity;
use Tests\App\Functional\EntityExtension\Model\ExtendedCategory\ExtendedCategory;
use Tests\App\Functional\EntityExtension\Model\ExtendedOrder\ExtendedOrder;
use Tests\App\Functional\EntityExtension\Model\ExtendedOrder\ExtendedOrderItem;
use Tests\App\Functional\EntityExtension\Model\ExtendedProduct\ExtendedProduct;
use Tests\App\Functional\EntityExtension\Model\ExtendedProduct\ExtendedProductTranslation;
use Tests\App\Functional\EntityExtension\Model\ExtendedProduct\ProductManyToManyBidirectionalEntity;
use Tests\App\Functional\EntityExtension\Model\ExtendedProduct\ProductOneToManyBidirectionalEntity;
use Tests\App\Functional\EntityExtension\Model\ExtendedProduct\ProductOneToOneBidirectionalEntity;
use Tests\App\Functional\EntityExtension\Model\Order\Order;
use Tests\App\Functional\EntityExtension\Model\Order\OrderItem;
use Tests\App\Functional\EntityExtension\Model\Product\Product;
use Tests\App\Functional\EntityExtension\Model\Product\ProductTranslation;
use Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity;
use Tests\App\Test\TransactionFunctionalTestCase;

class EntityExtensionTest extends TransactionFunctionalTestCase
{
    protected const MAIN_PRODUCT_ID = 1;
    protected const ONE_TO_ONE_SELF_REFERENCING_PRODUCT_ID = 2;
    protected const ONE_TO_MANY_SELF_REFERENCING_PRODUCT_ID = 3;
    protected const MANY_TO_MANY_SELF_REFERENCING_PRODUCT_ID = 4;

    protected const MAIN_CATEGORY_ID = 1;
    protected const ONE_TO_ONE_SELF_REFERENCING_CATEGORY_ID = 2;
    protected const ONE_TO_MANY_SELF_REFERENCING_CATEGORY_ID = 3;
    protected const MANY_TO_MANY_SELF_REFERENCING_CATEGORY_ID = 4;

    protected const ORDER_ITEM_ID = 1;

    /**
     * @inject
     */
    private EntityExtensionTestHelper $entityExtensionTestHelper;

    /**
     * @inject
     */
    private DatabaseSchemaFacade $databaseSchemaFacade;

    protected function setUp(): void
    {
        parent::setUp();

        if (!$this->isMonorepo()) {
            $this->markTestSkipped('This test is run only in monorepo.');
        }

        // To ensure the changes in the application do not break this test, it's necessary to start with a clean database
        // test database is restored after the test thanks to the usage of transactional test case
        $this->databaseSchemaFacade->dropSchemaIfExists('public');
        $this->databaseSchemaFacade->createSchema('public');

        $this->entityExtensionTestHelper->registerTestEntities();

        $entityExtensionMap = [
            Product::class => ExtendedProduct::class,
            Category::class => ExtendedCategory::class,
            OrderItem::class => ExtendedOrderItem::class,
            Order::class => ExtendedOrder::class,
            ProductTranslation::class => ExtendedProductTranslation::class,
        ];

        $newEntities = [
            UnidirectionalEntity::class,
            ProductOneToOneBidirectionalEntity::class,
            ProductManyToManyBidirectionalEntity::class,
            ProductOneToManyBidirectionalEntity::class,
            CategoryOneToOneBidirectionalEntity::class,
            CategoryManyToManyBidirectionalEntity::class,
            CategoryOneToManyBidirectionalEntity::class,
        ];

        $this->entityExtensionTestHelper->overwriteEntityExtensionMapInServicesInContainer($entityExtensionMap);

        $testEntities = array_merge($newEntities, array_values($entityExtensionMap));
        $metadata = $this->getMetadata($testEntities);

        $this->generateProxies($metadata);
        $this->updateDatabaseSchema($metadata);

        $this->insertTestEntities();
    }

    /**
     * @param string[] $entities
     * @return \Doctrine\Persistence\Mapping\ClassMetadata[]
     */
    private function getMetadata(array $entities): array
    {
        return array_map(function (string $entity) {
            return $this->em->getClassMetadata($entity);
        }, $entities);
    }

    /**
     * @param \Doctrine\Persistence\Mapping\ClassMetadata[] $metadata
     */
    private function generateProxies(array $metadata): void
    {
        $this->em->getProxyFactory()->generateProxyClasses($metadata);
    }

    /**
     * @param \Doctrine\Persistence\Mapping\ClassMetadata[] $metadata
     */
    private function updateDatabaseSchema(array $metadata): void
    {
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->updateSchema($metadata);
    }

    /**
     * Test mapping, so changes in source entity are ok
     */
    public function testMappingIsOk(): void
    {
        $validator = new SchemaValidator($this->em);
        $mappingValidation = $validator->validateMapping();

        foreach ($mappingValidation as $errors) {
            foreach ($errors as $error) {
                $this->fail($error);
            }
        }
    }

    /**
     * Test everything at once because setUp is slow
     */
    public function testAll(): void
    {
        $this->doTestExtendedProductPersistence();
        $this->doTestExtendedCategoryPersistence();
        $this->doTestExtendedOrderItemsPersistence();

        $this->doTestExtendedEntityInstantiation(Product::class, ExtendedProduct::class, self::MAIN_PRODUCT_ID);
        $this->doTestExtendedEntityInstantiation(Category::class, ExtendedCategory::class, self::MAIN_CATEGORY_ID);
        $this->doTestExtendedEntityInstantiation(OrderItem::class, ExtendedOrderItem::class, self::ORDER_ITEM_ID);
        $this->doTestExtendedEntityInstantiation(
            ProductTranslation::class,
            ExtendedProductTranslation::class,
            self::MAIN_PRODUCT_ID,
        );
    }

    private function doTestExtendedProductPersistence(): void
    {
        $product = $this->getProduct(self::MAIN_PRODUCT_ID);

        $product->setStringField('main product string');

        $foundManyToOneUnidirectionalEntity = new UnidirectionalEntity('many-to-one unidirectional');
        $this->em->persist($foundManyToOneUnidirectionalEntity);
        $product->setManyToOneUnidirectionalEntity($foundManyToOneUnidirectionalEntity);

        $oneToOneUnidirectionalEntity = new UnidirectionalEntity('one-to-one unidirectional');
        $this->em->persist($oneToOneUnidirectionalEntity);
        $product->setOneToOneUnidirectionalEntity($oneToOneUnidirectionalEntity);

        $oneToOneBidirectionalEntity = new ProductOneToOneBidirectionalEntity('one-to-one bidirectional');
        $this->em->persist($oneToOneBidirectionalEntity);
        $product->setOneToOneBidirectionalEntity($oneToOneBidirectionalEntity);

        $oneToOneSelfReferencingEntity = $this->getProduct(self::ONE_TO_ONE_SELF_REFERENCING_PRODUCT_ID);
        $product->setOneToOneSelfReferencingEntity($oneToOneSelfReferencingEntity);

        $oneToManyBidirectionalEntity = new ProductOneToManyBidirectionalEntity('one-to-many bidirectional');
        $this->em->persist($oneToManyBidirectionalEntity);
        $product->addOneToManyBidirectionalEntity($oneToManyBidirectionalEntity);

        $oneToManyUnidirectionalWithJoinTableEntity = new UnidirectionalEntity(
            'one-to-many unidirectional with join table',
        );
        $this->em->persist($oneToManyUnidirectionalWithJoinTableEntity);
        $product->addOneToManyUnidirectionalWithJoinTableEntity($oneToManyUnidirectionalWithJoinTableEntity);

        $oneToManySelfReferencingEntity = $this->getProduct(self::ONE_TO_MANY_SELF_REFERENCING_PRODUCT_ID);
        $product->addOneToManySelfReferencingEntity($oneToManySelfReferencingEntity);

        $manyToManyUnidirectionalEntity = new UnidirectionalEntity('many-to-many unidirectional');
        $this->em->persist($manyToManyUnidirectionalEntity);
        $product->addManyToManyUnidirectionalEntity($manyToManyUnidirectionalEntity);

        $manyToManyBidirectionalEntity = new ProductManyToManyBidirectionalEntity('many-to-many bidirectional');
        $this->em->persist($manyToManyBidirectionalEntity);
        $product->addManyToManyBidirectionalEntity($manyToManyBidirectionalEntity);

        $manyToManySelfReferencingEntity = $this->getProduct(self::MANY_TO_MANY_SELF_REFERENCING_PRODUCT_ID);
        $product->addManyToManySelfReferencingEntity($manyToManySelfReferencingEntity);

        $this->em->flush();
        $this->em->clear();

        $foundProduct = $this->getProduct(self::MAIN_PRODUCT_ID);

        $this->assertSame('main product string', $foundProduct->getStringField());

        $foundManyToOneUnidirectionalEntity = $foundProduct->getManyToOneUnidirectionalEntity();
        $this->assertInstanceOf(UnidirectionalEntity::class, $foundManyToOneUnidirectionalEntity);
        $this->assertSame('many-to-one unidirectional', $foundManyToOneUnidirectionalEntity->getName());

        $foundOneToOneUnidirectionalEntity = $foundProduct->getOneToOneUnidirectionalEntity();
        $this->assertInstanceOf(UnidirectionalEntity::class, $foundOneToOneUnidirectionalEntity);
        $this->assertSame('one-to-one unidirectional', $foundOneToOneUnidirectionalEntity->getName());

        $foundOneToOneBidirectionalEntity = $foundProduct->getOneToOneBidirectionalEntity();
        $this->assertInstanceOf(ProductOneToOneBidirectionalEntity::class, $foundOneToOneBidirectionalEntity);
        $this->assertSame('one-to-one bidirectional', $foundOneToOneBidirectionalEntity->getName());
        $this->assertSame($foundProduct, $foundOneToOneBidirectionalEntity->getProduct());

        $foundOneToOneSelfReferencingEntity = $foundProduct->getOneToOneSelfReferencingEntity();
        $this->assertInstanceOf(ExtendedProduct::class, $foundOneToOneSelfReferencingEntity);
        $this->assertSame(self::ONE_TO_ONE_SELF_REFERENCING_PRODUCT_ID, $foundOneToOneSelfReferencingEntity->getId());

        $foundOneToManyBidirectionalEntities = $foundProduct->getOneToManyBidirectionalEntities();
        $this->assertCount(1, $foundOneToManyBidirectionalEntities);
        $foundOneToManyBidirectionalEntity = reset($foundOneToManyBidirectionalEntities);
        $this->assertSame('one-to-many bidirectional', $foundOneToManyBidirectionalEntity->getName());

        $foundOneToManyUnidirectionalWithJoinTableEntities = $foundProduct->getOneToManyUnidirectionalWithJoinTableEntities();
        $this->assertCount(1, $foundOneToManyUnidirectionalWithJoinTableEntities);
        $foundOneToManyUnidirectionalWithJoinTableEntity = reset($foundOneToManyUnidirectionalWithJoinTableEntities);
        $this->assertSame(
            'one-to-many unidirectional with join table',
            $foundOneToManyUnidirectionalWithJoinTableEntity->getName(),
        );

        $foundOneToManySelfReferencingEntities = $foundProduct->getOneToManySelfReferencingEntities();
        $this->assertCount(1, $foundOneToManySelfReferencingEntities);
        $foundOneToManySelfReferencingEntity = reset($foundOneToManySelfReferencingEntities);
        $this->assertInstanceOf(ExtendedProduct::class, $foundOneToManySelfReferencingEntity);
        $this->assertSame(
            self::ONE_TO_MANY_SELF_REFERENCING_PRODUCT_ID,
            $foundOneToManySelfReferencingEntity->getId(),
        );
        $this->assertSame(
            $foundProduct,
            $foundOneToManySelfReferencingEntity->getOneToManySelfReferencingInverseEntity(),
        );

        $foundManyToManyUnidirectionalEntities = $foundProduct->getManyToManyUnidirectionalEntities();
        $this->assertCount(1, $foundManyToManyUnidirectionalEntities);
        $foundManyToManyUnidirectionalEntity = reset($foundManyToManyUnidirectionalEntities);
        $this->assertInstanceOf(UnidirectionalEntity::class, $foundManyToManyUnidirectionalEntity);
        $this->assertSame('many-to-many unidirectional', $foundManyToManyUnidirectionalEntity->getName());

        $foundManyToManyBidirectionalEntities = $foundProduct->getManyToManyBidirectionalEntities();
        $this->assertCount(1, $foundManyToManyBidirectionalEntities);
        $foundManyToManyBidirectionalEntity = reset($foundManyToManyBidirectionalEntities);
        $this->assertInstanceOf(ProductManyToManyBidirectionalEntity::class, $foundManyToManyBidirectionalEntity);
        $this->assertSame('many-to-many bidirectional', $foundManyToManyBidirectionalEntity->getName());

        $foundManyToManySelfReferencingEntities = $foundProduct->getManyToManySelfReferencingEntities();
        $this->assertCount(1, $foundManyToManySelfReferencingEntities);
        $foundManyToManySelfReferencingEntity = reset($foundManyToManySelfReferencingEntities);
        $this->assertInstanceOf(ExtendedProduct::class, $foundManyToManySelfReferencingEntity);
        $this->assertSame(
            self::MANY_TO_MANY_SELF_REFERENCING_PRODUCT_ID,
            $foundManyToManySelfReferencingEntity->getId(),
        );
        $foundManyToManySelfReferencingInverseEntities = $foundManyToManySelfReferencingEntity->getManyToManySelfReferencingInverseEntities();
        $this->assertCount(1, $foundManyToManySelfReferencingInverseEntities);
        $foundManyToManySelfReferencingInverseEntity = reset($foundManyToManySelfReferencingInverseEntities);
        $this->assertInstanceOf(ExtendedProduct::class, $foundManyToManySelfReferencingInverseEntity);
        $this->assertSame($foundProduct, $foundManyToManySelfReferencingInverseEntity);
    }

    /**
     * @param int $id
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedProduct\ExtendedProduct
     */
    private function getProduct(int $id): ExtendedProduct
    {
        $qb = $this->em->createQueryBuilder();
        $qb->from(ExtendedProduct::class, 'p')
            ->select('p')
            ->where('p.id = :id')
            ->setParameter(':id', $id);
        $query = $qb->getQuery();
        $product = $query->getSingleResult();
        $this->assertInstanceOf(ExtendedProduct::class, $product);

        return $product;
    }

    private function doTestExtendedCategoryPersistence(): void
    {
        $category = $this->getCategory(self::MAIN_CATEGORY_ID);

        $category->setStringField('main category string');

        $manyToOneUnidirectionalEntity = new UnidirectionalEntity('many-to-one unidirectional');
        $this->em->persist($manyToOneUnidirectionalEntity);
        $category->setManyToOneUnidirectionalEntity($manyToOneUnidirectionalEntity);

        $oneToOneUnidirectionalEntity = new UnidirectionalEntity('one-to-one unidirectional');
        $this->em->persist($oneToOneUnidirectionalEntity);
        $category->setOneToOneUnidirectionalEntity($oneToOneUnidirectionalEntity);

        $oneToOneBidirectionalEntity = new CategoryOneToOneBidirectionalEntity('one-to-one bidirectional');
        $this->em->persist($oneToOneBidirectionalEntity);
        $category->setOneToOneBidirectionalEntity($oneToOneBidirectionalEntity);

        $oneToOneSelfReferencingEntity = $this->getCategory(self::ONE_TO_ONE_SELF_REFERENCING_CATEGORY_ID);
        $category->setOneToOneSelfReferencingEntity($oneToOneSelfReferencingEntity);

        $oneToManyBidirectionalEntity = new CategoryOneToManyBidirectionalEntity('one-to-many bidirectional');
        $this->em->persist($oneToManyBidirectionalEntity);
        $category->addOneToManyBidirectionalEntity($oneToManyBidirectionalEntity);

        $oneToManyUnidirectionalWithJoinTableEntity = new UnidirectionalEntity(
            'one-to-many unidirectional with join table',
        );
        $this->em->persist($oneToManyUnidirectionalWithJoinTableEntity);
        $category->addOneToManyUnidirectionalWithJoinTableEntity($oneToManyUnidirectionalWithJoinTableEntity);

        $oneToManySelfReferencingEntity = $this->getCategory(self::ONE_TO_MANY_SELF_REFERENCING_CATEGORY_ID);
        $category->addOneToManySelfReferencingEntity($oneToManySelfReferencingEntity);

        $manyToManyUnidirectionalEntity = new UnidirectionalEntity('many-to-many unidirectional');
        $this->em->persist($manyToManyUnidirectionalEntity);
        $category->addManyToManyUnidirectionalEntity($manyToManyUnidirectionalEntity);

        $manyToManyBidirectionalEntity = new CategoryManyToManyBidirectionalEntity('many-to-many bidirectional');
        $this->em->persist($manyToManyBidirectionalEntity);
        $category->addManyToManyBidirectionalEntity($manyToManyBidirectionalEntity);

        $manyToManySelfReferencingEntity = $this->getCategory(self::MANY_TO_MANY_SELF_REFERENCING_CATEGORY_ID);
        $category->addManyToManySelfReferencingEntity($manyToManySelfReferencingEntity);

        $this->em->flush();
        $this->em->clear();

        $foundCategory = $this->getCategory(self::MAIN_CATEGORY_ID);

        $this->assertSame('main category string', $foundCategory->getStringField());

        $foundManyToOneUnidirectionalEntity = $foundCategory->getManyToOneUnidirectionalEntity();
        $this->assertInstanceOf(UnidirectionalEntity::class, $foundManyToOneUnidirectionalEntity);
        $this->assertSame('many-to-one unidirectional', $foundManyToOneUnidirectionalEntity->getName());

        $foundOneToOneUnidirectionalEntity = $foundCategory->getOneToOneUnidirectionalEntity();
        $this->assertInstanceOf(UnidirectionalEntity::class, $foundOneToOneUnidirectionalEntity);
        $this->assertSame('one-to-one unidirectional', $foundOneToOneUnidirectionalEntity->getName());

        $foundOneToOneBidirectionalEntity = $foundCategory->getOneToOneBidirectionalEntity();
        $this->assertInstanceOf(CategoryOneToOneBidirectionalEntity::class, $foundOneToOneBidirectionalEntity);
        $this->assertSame('one-to-one bidirectional', $foundOneToOneBidirectionalEntity->getName());
        $this->assertSame($foundCategory, $foundOneToOneBidirectionalEntity->getCategory());

        $foundOneToOneSelfReferencingEntity = $foundCategory->getOneToOneSelfReferencingEntity();
        $this->assertInstanceOf(ExtendedCategory::class, $foundOneToOneSelfReferencingEntity);
        $this->assertSame(self::ONE_TO_ONE_SELF_REFERENCING_CATEGORY_ID, $foundOneToOneSelfReferencingEntity->getId());

        $foundOneToManyBidirectionalEntities = $foundCategory->getOneToManyBidirectionalEntities();
        $this->assertCount(1, $foundOneToManyBidirectionalEntities);
        $foundOneToManyBidirectionalEntity = reset($foundOneToManyBidirectionalEntities);
        $this->assertSame('one-to-many bidirectional', $foundOneToManyBidirectionalEntity->getName());

        $foundOneToManyUnidirectionalWithJoinTableEntities = $foundCategory->getOneToManyUnidirectionalWithJoinTableEntities();
        $this->assertCount(1, $foundOneToManyUnidirectionalWithJoinTableEntities);
        $foundOneToManyUnidirectionalWithJoinTableEntity = reset($foundOneToManyUnidirectionalWithJoinTableEntities);
        $this->assertSame(
            'one-to-many unidirectional with join table',
            $foundOneToManyUnidirectionalWithJoinTableEntity->getName(),
        );

        $foundOneToManySelfReferencingEntities = $foundCategory->getOneToManySelfReferencingEntities();
        $this->assertCount(1, $foundOneToManySelfReferencingEntities);
        $foundOneToManySelfReferencingEntity = reset($foundOneToManySelfReferencingEntities);
        $this->assertInstanceOf(ExtendedCategory::class, $foundOneToManySelfReferencingEntity);
        $this->assertSame(
            self::ONE_TO_MANY_SELF_REFERENCING_CATEGORY_ID,
            $foundOneToManySelfReferencingEntity->getId(),
        );
        $this->assertSame(
            $foundCategory,
            $foundOneToManySelfReferencingEntity->getOneToManySelfReferencingInverseEntity(),
        );

        $foundManyToManyUnidirectionalEntities = $foundCategory->getManyToManyUnidirectionalEntities();
        $this->assertCount(1, $foundManyToManyUnidirectionalEntities);
        $foundManyToManyUnidirectionalEntity = reset($foundManyToManyUnidirectionalEntities);
        $this->assertInstanceOf(UnidirectionalEntity::class, $foundManyToManyUnidirectionalEntity);
        $this->assertSame('many-to-many unidirectional', $foundManyToManyUnidirectionalEntity->getName());

        $foundManyToManyBidirectionalEntities = $foundCategory->getManyToManyBidirectionalEntities();
        $this->assertCount(1, $foundManyToManyBidirectionalEntities);
        $foundManyToManyBidirectionalEntity = reset($foundManyToManyBidirectionalEntities);
        $this->assertInstanceOf(CategoryManyToManyBidirectionalEntity::class, $foundManyToManyBidirectionalEntity);
        $this->assertSame('many-to-many bidirectional', $foundManyToManyBidirectionalEntity->getName());

        $foundManyToManySelfReferencingEntities = $foundCategory->getManyToManySelfReferencingEntities();
        $this->assertCount(1, $foundManyToManySelfReferencingEntities);
        $foundManyToManySelfReferencingEntity = reset($foundManyToManySelfReferencingEntities);
        $this->assertInstanceOf(ExtendedCategory::class, $foundManyToManySelfReferencingEntity);
        $this->assertSame(
            self::MANY_TO_MANY_SELF_REFERENCING_CATEGORY_ID,
            $foundManyToManySelfReferencingEntity->getId(),
        );
        $foundManyToManySelfReferencingInverseEntities = $foundManyToManySelfReferencingEntity->getManyToManySelfReferencingInverseEntities();
        $this->assertCount(1, $foundManyToManySelfReferencingInverseEntities);
        $foundManyToManySelfReferencingInverseEntity = reset($foundManyToManySelfReferencingInverseEntities);
        $this->assertInstanceOf(ExtendedCategory::class, $foundManyToManySelfReferencingInverseEntity);
        $this->assertSame($foundCategory, $foundManyToManySelfReferencingInverseEntity);
    }

    /**
     * @param int $id
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedCategory\ExtendedCategory
     */
    public function getCategory(int $id): ExtendedCategory
    {
        $qb = $this->em->createQueryBuilder();
        $qb->from(ExtendedCategory::class, 'c')
            ->select('c')
            ->where('c.id = :id')
            ->setParameter(':id', $id);
        $query = $qb->getQuery();
        $category = $query->getSingleResult();
        $this->assertInstanceOf(ExtendedCategory::class, $category);

        return $category;
    }

    private function doTestExtendedOrderItemsPersistence(): void
    {
        $orderItem = $this->getOrderItem(self::ORDER_ITEM_ID);
        $orderItem->setStringField('string value');

        $this->em->flush();
        $this->em->clear();

        $foundItem = $this->getOrderItem(self::ORDER_ITEM_ID);
        $this->assertSame('string value', $foundItem->getStringField());
    }

    /**
     * @param int $id
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedOrder\ExtendedOrderItem
     */
    private function getOrderItem(int $id): ExtendedOrderItem
    {
        $qb = $this->em->createQueryBuilder();
        $qb->from(ExtendedOrderItem::class, 'i')
            ->select('i')
            ->where('i.id = :id')
            ->setParameter(':id', $id);
        $query = $qb->getQuery();
        $result = $query->getSingleResult();
        $this->assertInstanceOf(ExtendedOrderItem::class, $result);

        return $result;
    }

    /**
     * @param string $parentEntityName
     * @param string $extendedEntityName
     * @param int $entityId
     */
    private function doTestExtendedEntityInstantiation(
        string $parentEntityName,
        string $extendedEntityName,
        int $entityId,
    ): void {
        $repository = $this->em->getRepository($parentEntityName);
        $this->assertInstanceOf($extendedEntityName, $repository->find($entityId));

        $query = $this->em->createQuery('SELECT x FROM ' . $parentEntityName . ' x WHERE x.id = :id')
            ->setParameter('id', $entityId);
        $this->assertInstanceOf($extendedEntityName, $query->getSingleResult());

        $qb = $this->em->createQueryBuilder();
        $qb->from($parentEntityName, 'x')
            ->select('x')
            ->where('x.id = :id')
            ->setParameter(':id', $entityId);
        $this->assertInstanceOf($extendedEntityName, $qb->getQuery()->getSingleResult());

        $entity = $this->em->find($parentEntityName, $entityId);
        $this->assertInstanceOf($extendedEntityName, $entity);
    }

    private function insertTestEntities(): void
    {
        $this->insertProduct();
        $this->insertProduct();
        $this->insertProduct();
        $this->insertProduct();

        $this->insertCategory();
        $this->insertCategory();
        $this->insertCategory();
        $this->insertCategory();

        $this->insertOrder();
    }

    private function insertProduct(): void
    {
        $product = new ExtendedProduct();

        $product->setMandatoryData();

        $this->em->persist($product);
        $this->em->flush();
    }

    private function insertCategory(): void
    {
        $category = new ExtendedCategory();

        $category->setMandatoryData();

        $this->em->persist($category);
        $this->em->flush();
    }

    private function insertOrder(): void
    {
        $order = new ExtendedOrder();
        $orderItem = new ExtendedOrderItem($order);

        $order->setMandatoryData();
        $orderItem->setMandatoryData();

        $this->em->persist($order);
        $this->em->persist($orderItem);
        $this->em->flush();
    }

    /**
     * @return bool
     */
    private function isMonorepo(): bool
    {
        return file_exists(__DIR__ . '/../../../../../../parameters_monorepo.yaml');
    }
}
