<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Maker;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;

class DataFixtureMaker extends BaseMaker
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getCommandName(): string
    {
        return 'make:shopsys:data-fixture';
    }

    /**
     * {@inheritdoc}
     */
    public static function getCommandDescription(): string
    {
        return 'Create a new data fixture class';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getTemplateName(): string
    {
        return __DIR__ . '/templates/DataFixture.tpl.php';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getGeneratedClassNamespace(): string
    {
        return 'DataFixtures\\Demo\\';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getGeneratedClassSuffix(): string
    {
        return 'DataFixture';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getUseStatements(): array
    {
        return [
            AbstractReferenceFixture::class,
            ObjectManager::class,
            DependentFixtureInterface::class,
            $this->entityFullyQualifiedName . 'Data',
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getConstructorDependencies(): array
    {
        return [
            $this->entityFullyQualifiedName . 'Facade',
            $this->entityFullyQualifiedName . 'DataFactory',
        ];
    }
}
