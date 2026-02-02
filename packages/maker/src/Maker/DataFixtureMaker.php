<?php

declare(strict_types=1);

namespace Shopsys\MakerBundle\Maker;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;

class DataFixtureMaker extends BaseMaker
{
    #[Override]
    public static function getCommandName(): string
    {
        return 'make:shopsys:data-fixture';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a new data fixture class';
    }

    #[Override]
    protected function getTemplateName(): string
    {
        return __DIR__ . '/../../templates/DataFixture.tpl.php';
    }

    #[Override]
    protected function getGeneratedClassNamespace(): string
    {
        return 'DataFixtures\\Demo\\';
    }

    #[Override]
    protected function getGeneratedClassSuffix(): string
    {
        return 'DataFixture';
    }

    #[Override]
    protected function getUseStatements(): array
    {
        return [
            AbstractReferenceFixture::class,
            ObjectManager::class,
            DependentFixtureInterface::class,
            $this->entityConfig->getEntityFullyQualifiedName() . 'Data',
        ];
    }

    #[Override]
    protected function getConstructorDependencies(): array
    {
        return [
            $this->entityConfig->getEntityFullyQualifiedName() . 'Facade',
            $this->entityConfig->getEntityFullyQualifiedName() . 'DataFactory',
        ];
    }
}
