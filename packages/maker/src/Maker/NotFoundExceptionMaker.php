<?php

declare(strict_types=1);

namespace Shopsys\MakerBundle\Maker;

use Override;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotFoundExceptionMaker extends BaseMaker
{
    #[Override]
    public static function getCommandName(): string
    {
        return 'make:shopsys:not-found-exception';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a new entity not found exception class';
    }

    #[Override]
    protected function getTemplateName(): string
    {
        return __DIR__ . '/../../templates/NotFoundException.tpl.php';
    }

    #[Override]
    protected function getGeneratedClassSuffix(): string
    {
        return 'NotFoundException';
    }

    #[Override]
    protected function getUseStatements(): array
    {
        return [
            NotFoundHttpException::class,
        ];
    }

    #[Override]
    protected function getConstructorDependencies(): array
    {
        return [];
    }

    #[Override]
    protected function getGeneratedClassNamespace(): string
    {
        return parent::getGeneratedClassNamespace() . 'Exception';
    }
}
