<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud;

/**
 * Controllers handling CRUD routes get the Definition of the CRUD controller from CrudControllerInitializer before the action runs.
 * Implemented by AbstractCrudController and AbstractCrudControllerExtension via CrudControllerTrait, any other controller
 * handling a custom CRUD action can implement it (and use the trait) as well.
 */
interface CrudDefinitionAwareInterface
{
    public function setDefinition(Definition $definition): void;
}
