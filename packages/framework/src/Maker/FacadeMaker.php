<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Maker;

use Doctrine\ORM\EntityManagerInterface;
use Override;

class FacadeMaker extends BaseMaker
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getCommandName(): string
    {
        return 'make:shopsys:facade';
    }

    /**
     * {@inheritdoc}
     */
    public static function getCommandDescription(): string
    {
        return 'Create a new facade class for the given entity';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getTemplateName(): string
    {
        return __DIR__ . '/templates/Facade.tpl.php';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getGeneratedClassSuffix(): string
    {
        return 'Facade';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getUseStatements(): array
    {
        return [
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
            'em' => EntityManagerInterface::class,
            $this->entityFullyQualifiedName . 'Repository',
        ];
    }
}
