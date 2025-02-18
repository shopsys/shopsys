<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Maker;

use Override;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotFoundExceptionMaker extends BaseMaker
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getCommandName(): string
    {
        return 'make:shopsys:not-found-exception';
    }

    /**
     * {@inheritdoc}
     */
    public static function getCommandDescription(): string
    {
        return 'Create a new entity not found exception class';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getTemplateName(): string
    {
        return __DIR__ . '/templates/NotFoundException.tpl.php';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getGeneratedClassSuffix(): string
    {
        return 'NotFoundException';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getUseStatements(): array
    {
        return [
            NotFoundHttpException::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getConstructorDependencies(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getGeneratedClassNamespace(): string
    {
        return parent::getGeneratedClassNamespace() . 'Exception';
    }
}
