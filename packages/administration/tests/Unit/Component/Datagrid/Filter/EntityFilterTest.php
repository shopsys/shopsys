<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Filter;

use DateTimeZone;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\FilterNotApplicableException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\EntityFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterEnvironment;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterRuleData;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\ProductFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathCardinalityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Symfony\Bridge\Doctrine\Form\ChoiceList\DoctrineChoiceLoader;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyArticle;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyEntityManagerFactory;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyTag;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;

final class EntityFilterTest extends TestCase
{
    public function testRelatedEntitiesAreComparedByTheirIdentifiers(): void
    {
        $filter = $this->resolve(EntityFilter::new('author.publisher'));

        $condition = $filter->buildCondition($this->createRule(ExpressionOperatorEnum::IN, [new TestEntity(3), new TestEntity(5)]));

        $this->assertEquals(Condition::in('author.publisher.id', [3, 5]), $condition);
    }

    public function testMissingRelationIsTestedOnTheAssociationItself(): void
    {
        $filter = $this->resolve(EntityFilter::new('author.publisher'));

        $this->assertEquals(Condition::isNull('author.publisher'), $filter->buildCondition($this->createRule(ExpressionOperatorEnum::IS_NULL, null)));
    }

    /**
     * A single association is tested for being filled, a collection for having any related entity.
     */
    public function testMissingRelationIsOfferedByTheCardinalityOfThePath(): void
    {
        $toOneFilter = $this->resolve(EntityFilter::new('author.publisher'));
        $toManyFilter = EntityFilter::new('tags');
        $toManyFilter->resolveFor($this->createEnvironment([
            'tags' => new PathDescription('tags', PathCardinalityEnum::TO_MANY, PathValueTypeEnum::ASSOCIATION, TestEntity::class),
        ]));

        $this->assertSame([ExpressionOperatorEnum::IN, ExpressionOperatorEnum::NOT_IN, ExpressionOperatorEnum::IS_NULL, ExpressionOperatorEnum::IS_NOT_NULL], $toOneFilter->getOperators());
        $this->assertSame([ExpressionOperatorEnum::IN, ExpressionOperatorEnum::NOT_IN, ExpressionOperatorEnum::IS_EMPTY, ExpressionOperatorEnum::IS_NOT_EMPTY], $toManyFilter->getOperators());
        $this->assertEquals(Condition::isEmpty('tags'), $toManyFilter->buildCondition($this->createRule(ExpressionOperatorEnum::IS_EMPTY, null)));
        // none of the tags is the picked one, rather than some other tag being there
        $this->assertEquals(
            Condition::not(Condition::in('tags.id', [3])),
            $toManyFilter->buildCondition($this->createRule(ExpressionOperatorEnum::NOT_IN, [new TestEntity(3)])),
        );
    }

    public function testEntityClassIsLearntFromTheMedium(): void
    {
        $filter = $this->resolve(EntityFilter::new('author.publisher'));

        $this->assertSame(TestEntity::class, $filter->getValueFormOptions(ExpressionValueArityEnum::LIST)['class']);
        $this->assertTrue($filter->getValueFormOptions(ExpressionValueArityEnum::LIST)['multiple']);
    }

    public function testPathNotEndingWithAnAssociationIsRefused(): void
    {
        $this->expectException(FilterNotApplicableException::class);

        EntityFilter::new('name')->resolveFor($this->createEnvironment([
            'name' => new PathDescription('name', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::STRING),
        ]));
    }

    public function testUnknownEntityClassIsRefused(): void
    {
        $this->expectException(FilterNotApplicableException::class);

        EntityFilter::new('author.publisher')->resolveFor($this->createEnvironment([
            'author.publisher' => new PathDescription('author.publisher', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::ASSOCIATION),
        ]));
    }

    /**
     * A submitted identifier is looked up among the offered entities by the value of the choice, never by a query
     * of its own — an identifier beyond the range of its column would fail that query.
     */
    public function testSubmittedIdentifierIsLookedUpAmongTheOfferedEntities(): void
    {
        $choiceValue = $this->resolve(EntityFilter::new('author.publisher'))->getValueFormOptions(ExpressionValueArityEnum::LIST)['choice_value'];
        $entityLoaderMock = $this->createMock(EntityLoaderInterface::class);
        $entityLoaderMock->method('getEntities')->willReturn([new TestEntity(5)]);
        $entityLoaderMock->expects($this->never())->method('getEntitiesByIds');
        $choiceLoader = new DoctrineChoiceLoader($this->createStub(ObjectManager::class), TestEntity::class, null, $entityLoaderMock);

        $this->assertEquals([new TestEntity(5)], array_values($choiceLoader->loadChoicesForValues(['5', '2147483648'], $choiceValue)));
        $this->assertSame('5', $choiceValue(new TestEntity(5)));
        $this->assertNull($choiceValue(null));
    }

    /**
     * A translated name is read from the translations, so they are fetched with the entities by a single query.
     */
    public function testOfferedEntitiesAreFetchedWithTheirTranslations(): void
    {
        $entityManager = DummyEntityManagerFactory::create();
        $createQueryBuilder = $this->resolve(EntityFilter::new('author.publisher'))->getValueFormOptions(ExpressionValueArityEnum::LIST)['query_builder'];

        $translatableQueryBuilder = $createQueryBuilder($entityManager->getRepository(DummyArticle::class));
        $plainQueryBuilder = $createQueryBuilder($entityManager->getRepository(DummyTag::class));

        $this->assertSame('SELECT entity, translation FROM ' . DummyArticle::class . ' entity LEFT JOIN entity.translations translation', $translatableQueryBuilder->getDQL());
        $this->assertSame('SELECT entity FROM ' . DummyTag::class . ' entity', $plainQueryBuilder->getDQL());
    }

    public function testProductFilterComparesTheSinglePickedProduct(): void
    {
        $filter = $this->resolve(ProductFilter::new('product'), 'product', Product::class);

        $this->assertEquals(Condition::notEquals('product.id', 7), $filter->buildCondition($this->createRule(ExpressionOperatorEnum::NOT_EQUALS, new TestEntity(7))));
    }

    /**
     * The picker picks a product, so comparing its identifier with another entity would match nonsense.
     */
    public function testProductFilterOverAnotherEntityIsRefused(): void
    {
        $this->expectException(FilterNotApplicableException::class);
        $this->expectExceptionMessage('has to lead to a product');

        $this->resolve(ProductFilter::new('category'), 'category');
    }

    /**
     * @param class-string $targetEntityClass
     */
    private function resolve(
        EntityFilter $filter,
        string $path = 'author.publisher',
        string $targetEntityClass = TestEntity::class,
    ): EntityFilter {
        $filter->resolveFor($this->createEnvironment([
            $path => new PathDescription($path, PathCardinalityEnum::TO_ONE, PathValueTypeEnum::ASSOCIATION, $targetEntityClass),
        ]));

        return $filter;
    }

    /**
     * @param array<string, \Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription> $pathDescriptions
     */
    private function createEnvironment(array $pathDescriptions = []): FilterEnvironment
    {
        return new FilterEnvironment(
            new CapturingAdapter($this->createStub(DataSourceInterface::class), $pathDescriptions),
            new ExpressionOperatorEnum(),
            new DateTimeZone('Europe/Prague'),
        );
    }

    private function createRule(string $operator, mixed $value): FilterRuleData
    {
        $rule = new FilterRuleData();
        $rule->operator = $operator;
        $rule->value = $value;

        return $rule;
    }
}
