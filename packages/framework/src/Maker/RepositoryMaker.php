<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Maker;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Override;

class RepositoryMaker extends BaseMaker
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getCommandName(): string
    {
        return 'make:shopsys:repository';
    }

    /**
     * {@inheritdoc}
     */
    public static function getCommandDescription(): string
    {
        return 'Create a new repository class for the given entity';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getTemplateName(): string
    {
        return __DIR__ . '/templates/Repository.tpl.php';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getGeneratedClassSuffix(): string
    {
        return 'Repository';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getUseStatements(): array
    {
        return [
            EntityRepository::class,
            $this->entityNamespace . 'Exception\\' . $this->entityName . 'NotFoundException',
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getConstructorDependencies(): array
    {
        return [
            EntityManagerInterface::class,
        ];
    }
}
