<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery;

/**
 * Creates a real ProxyQuery over hand-mapped test entities (a translatable product
 * with a many-to-one brand), so dot-notation resolution runs against real Doctrine metadata.
 */
trait ProxyQueryFactoryTrait
{
    private function createSearchProxyQuery(string $locale = 'en'): ProxyQuery
    {
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $reflectionService = new RuntimeReflectionService();

        $productMetadata = new ClassMetadata(TestSearchProduct::class);
        $productMetadata->initializeReflection($reflectionService);
        $productMetadata->mapField(['fieldName' => 'id', 'type' => 'integer', 'id' => true]);
        $productMetadata->mapField(['fieldName' => 'catnum', 'type' => 'string']);
        $productMetadata->mapManyToOne(['fieldName' => 'brand', 'targetEntity' => TestSearchBrand::class]);
        $productMetadata->mapOneToMany([
            'fieldName' => 'translations',
            'targetEntity' => TestSearchProductTranslation::class,
            'mappedBy' => 'translatable',
        ]);

        $brandMetadata = new ClassMetadata(TestSearchBrand::class);
        $brandMetadata->initializeReflection($reflectionService);
        $brandMetadata->mapField(['fieldName' => 'id', 'type' => 'integer', 'id' => true]);
        $brandMetadata->mapField(['fieldName' => 'name', 'type' => 'string']);

        $translationMetadata = new ClassMetadata(TestSearchProductTranslation::class);
        $translationMetadata->initializeReflection($reflectionService);
        $translationMetadata->mapField(['fieldName' => 'id', 'type' => 'integer', 'id' => true]);
        $translationMetadata->mapField(['fieldName' => 'locale', 'type' => 'string']);
        $translationMetadata->mapField(['fieldName' => 'name', 'type' => 'string']);
        $translationMetadata->mapField(['fieldName' => 'description', 'type' => 'string']);

        $entityManager->method('getClassMetadata')->willReturnMap([
            [TestSearchProduct::class, $productMetadata],
            [TestSearchBrand::class, $brandMetadata],
            [TestSearchProductTranslation::class, $translationMetadata],
        ]);

        $queryBuilder = new QueryBuilder($entityManager);
        $queryBuilder->select('o')->from(TestSearchProduct::class, 'o');

        $repository = $this->createStub(EntityRepository::class);
        $repository->method('createQueryBuilder')->willReturn($queryBuilder);
        $entityManager->method('getRepository')->willReturn($repository);

        return new ProxyQuery(TestSearchProduct::class, $entityManager, $locale);
    }
}

class TestSearchProduct
{
    public int $id;

    public string $catnum;

    public ?TestSearchBrand $brand = null;

    public $translations;
}

class TestSearchBrand
{
    public int $id;

    public string $name;
}

class TestSearchProductTranslation
{
    public int $id;

    public string $locale;

    public string $name;

    public string $description;

    public $translatable;
}
