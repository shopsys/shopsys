<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Crud;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Crud\Action\Exception\CrudActionNotFoundException;
use Shopsys\AdministrationBundle\Component\Crud\Definition;
use Shopsys\AdministrationBundle\Component\Crud\Handler\HandlerInterface;
use Shopsys\AdministrationBundle\Component\Crud\Handler\ReadHandlerInterface;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use stdClass;
use Tests\AdministrationBundle\Unit\DependencyInjection\Compiler\Fixtures\ReviewCrudController;

class DefinitionTest extends TestCase
{
    private const string CONTROLLER_CLASS = ReviewCrudController::class;

    #[DataProvider('builtInActionDataProvider')]
    public function testGetActionReturnsBuiltInActionForEnumAndName(ActionType|string $action): void
    {
        $definition = $this->createDefinition();

        $actionDefinition = $definition->getAction($action);

        $this->assertSame('edit', $actionDefinition->name);
        $this->assertSame(self::CONTROLLER_CLASS . '::editAction', $actionDefinition->getControllerReference());
        $this->assertSame('/edit/{id}', $actionDefinition->path);
        $this->assertTrue($actionDefinition->entityBound);
        $this->assertSame('admin_crud_review_edit', $actionDefinition->getRouteName());
    }

    /**
     * @return iterable<string, array{action: \Shopsys\AdministrationBundle\Component\Config\ActionType|string}>
     */
    public static function builtInActionDataProvider(): iterable
    {
        yield 'enum' => [
            'action' => ActionType::EDIT,
        ];

        yield 'name' => [
            'action' => 'edit',
        ];
    }

    public function testGetActionReturnsCustomActionByName(): void
    {
        $definition = $this->createDefinition();

        $this->assertSame(self::CONTROLLER_CLASS . '::approveAction', $definition->getAction('approve')->getControllerReference());
    }

    public function testGetActionThrowsForUnknownNameAndListsAvailableActions(): void
    {
        $definition = $this->createDefinition();

        $this->expectException(CrudActionNotFoundException::class);
        $this->expectExceptionMessage('has no action "publish". Available actions: ');
        $this->expectExceptionMessage('approve');

        $definition->getAction('publish');
    }

    public function testGetReadHandlerReturnsFirstHandlerAbleToLoadRecords(): void
    {
        $readHandler = $this->createStub(ReadHandlerInterface::class);
        $definition = $this->createDefinition(handlers: [
            ActionType::CREATE->value => $this->createStub(HandlerInterface::class),
            ActionType::EDIT->value => $readHandler,
        ]);

        $this->assertSame($readHandler, $definition->getReadHandler());
    }

    public function testGetReadHandlerThrowsWhenNoHandlerCanLoadRecords(): void
    {
        $definition = $this->createDefinition(handlers: [ActionType::CREATE->value => null]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No handler implementing');

        $definition->getReadHandler();
    }

    /**
     * @param array<string, \Shopsys\AdministrationBundle\Component\Crud\Handler\HandlerInterface|null> $handlers
     */
    private function createDefinition(array $handlers = []): Definition
    {
        // ReadHandlerInterface handlers require a Presentable entity, so an anonymous Presentable class is used instead of stdClass
        $entityClass = $handlers === [] ? stdClass::class : (new class() implements Presentable {
            public function toHumanReadable(): string
            {
                return 'entity';
            }
        })::class;

        return new Definition(
            self::CONTROLLER_CLASS,
            'ReviewCrudController',
            $entityClass,
            'Review',
            new CrudConfig('Review')->getConfig(),
            [],
            $handlers,
            ReviewCrudControllerRegistryFactory::createActionDefinitions(),
        );
    }
}
