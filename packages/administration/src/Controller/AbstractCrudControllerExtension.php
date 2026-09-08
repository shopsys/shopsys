<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Shopsys\AdministrationBundle\Component\Config\ActionsConfig;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Crud\Form\CrudFormConfigurator;
use Shopsys\AdministrationBundle\Component\Crud\Template\CrudTemplateParameters;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
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

    public function configureForm(CrudFormConfigurator $formConfigurator, ?Presentable $entity = null): void
    {
    }

    /**
     * Sets additional variables passed to the template of the given action via `$templateParameters->set()`.
     * A name already used by the action itself (`title`, `form`, ...), by the CRUD controller
     * or by another extension throws an exception.
     *
     * @param \Shopsys\FrameworkBundle\Component\Utils\Presentable|null $entity Null for the list and create actions, the displayed entity otherwise
     */
    public function configureTemplateParameters(
        CrudTemplateParameters $templateParameters,
        ActionType $actionType,
        ?Presentable $entity = null,
    ): void {
    }
}
