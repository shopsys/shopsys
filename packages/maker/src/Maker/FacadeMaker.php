<?php

declare(strict_types=1);

namespace Shopsys\MakerBundle\Maker;

use Doctrine\ORM\EntityManagerInterface;
use Override;

class FacadeMaker extends BaseMaker
{
    #[Override]
    public static function getCommandName(): string
    {
        return 'make:shopsys:facade';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a new facade class for the given entity';
    }

    #[Override]
    protected function getTemplateName(): string
    {
        return __DIR__ . '/../../templates/Facade.tpl.php';
    }

    #[Override]
    protected function getGeneratedClassSuffix(): string
    {
        return 'Facade';
    }

    #[Override]
    protected function getUseStatements(): array
    {
        return [
            $this->entityConfig->getEntityFullyQualifiedName() . 'Data',
        ];
    }

    #[Override]
    protected function getConstructorDependencies(): array
    {
        return [
            'em' => EntityManagerInterface::class,
            $this->entityConfig->getEntityFullyQualifiedName() . 'Repository',
        ];
    }
}
