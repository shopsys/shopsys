<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Crud\CrudControllerRegistry;
use Shopsys\AdministrationBundle\Component\Crud\CrudRoleConstantProvider;
use Shopsys\AdministrationBundle\DependencyInjection\Compiler\ResolveCrudRoleConstantsCompilerPass;
use stdClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tests\AdministrationBundle\Unit\DependencyInjection\Compiler\Fixtures\InheritedModeratedReviewCrudController;
use Tests\AdministrationBundle\Unit\DependencyInjection\Compiler\Fixtures\ModeratedReviewCrudController;
use Tests\AdministrationBundle\Unit\DependencyInjection\Compiler\Fixtures\ReviewCrudController;

class ResolveCrudRoleConstantsCompilerPassTest extends TestCase
{
    public function testForRoleOfTheControllerOrItsParentIsResolvedTheSameWayTheAccessControlDoesIt(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter(CrudControllerRegistry::CRUD_CONTROLLERS_PARAMETER, [
            ['class' => ReviewCrudController::class, 'entityClass' => stdClass::class],
            ['class' => ModeratedReviewCrudController::class, 'entityClass' => stdClass::class],
            ['class' => InheritedModeratedReviewCrudController::class, 'entityClass' => stdClass::class],
        ]);
        $container->setParameter(CrudControllerRegistry::CRUD_CONTROLLERS_EXTENSIONS_PARAMETER, []);

        new ResolveCrudRoleConstantsCompilerPass()->process($container);

        $this->assertSame([
            ReviewCrudController::class => null,
            ModeratedReviewCrudController::class => 'ROLE_MODERATOR',
            InheritedModeratedReviewCrudController::class => 'ROLE_MODERATOR',
        ], $container->getParameter(CrudRoleConstantProvider::CRUD_ROLE_CONSTANTS_PARAMETER));
    }
}
