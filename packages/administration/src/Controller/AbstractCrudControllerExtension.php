<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Shopsys\AdministrationBundle\Component\Config\ActionsConfig;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Crud\Form\CrudFormConfigurator;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Component\Search\SearchConfig;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('shopsys.admin.crud_controllers')]
abstract class AbstractCrudControllerExtension extends AdminBaseController
{
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

    public function configureSearch(SearchConfig $search): void
    {
    }

    public function configureForm(CrudFormConfigurator $formConfigurator, ?object $entity = null): void
    {
    }
}
