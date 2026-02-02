<?php

declare(strict_types=1);

namespace Shopsys\MakerBundle\Maker;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Override;

class RepositoryMaker extends BaseMaker
{
    #[Override]
    public static function getCommandName(): string
    {
        return 'make:shopsys:repository';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a new repository class for the given entity';
    }

    #[Override]
    protected function getTemplateName(): string
    {
        return __DIR__ . '/../../templates/Repository.tpl.php';
    }

    #[Override]
    protected function getGeneratedClassSuffix(): string
    {
        return 'Repository';
    }

    #[Override]
    protected function getUseStatements(): array
    {
        return [
            EntityRepository::class,
            $this->entityConfig->getEntityNamespace() . 'Exception\\' . $this->entityConfig->entityName . 'NotFoundException',
        ];
    }

    #[Override]
    protected function getConstructorDependencies(): array
    {
        return [
            'em' => EntityManagerInterface::class,
        ];
    }
}
