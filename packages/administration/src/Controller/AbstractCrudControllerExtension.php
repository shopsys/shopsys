<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Shopsys\AdministrationBundle\Component\Config\ActionsConfig;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Crud\Definition;
use Shopsys\AdministrationBundle\Component\Crud\Form\CrudFormConfigurator;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('shopsys.admin.crud_controllers')]
abstract class AbstractCrudControllerExtension extends AdminBaseController
{
    /**
     * Definition of the extended CRUD controller, set by CrudControllerInitializer before the action runs.
     * Not available in configure() as the config is part of the Definition itself.
     */
    protected Definition $definition;

    public function setDefinition(Definition $definition): void
    {
        $this->definition = $definition;
    }

    public function configure(CrudConfig $config): void
    {
    }

    public function configureActions(ActionsConfig $actions): void
    {
    }

    public function configureDatagrid(Datagrid $datagrid): void
    {
    }

    public function configureQuery(QueryBuilder $queryBuilder): void
    {
    }

    public function configureForm(CrudFormConfigurator $formConfigurator, ?object $entity = null): void
    {
    }
}
