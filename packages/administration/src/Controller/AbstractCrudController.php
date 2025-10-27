<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Shopsys\AdministrationBundle\Component\Config\ActionsConfig;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Crud\Definition;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\OrmAdapterFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Component\Datagrid\DatagridFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractCrudController extends AbstractController
{
    public Definition $definition;

    #[Required]
    public DatagridFactory $datagridFactory;

    #[Required]
    public OrmAdapterFactory $ormAdapterFactory;

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\CrudConfig $config
     */
    public function configure(CrudConfig $config): void
    {
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionsConfig $actions
     */
    protected function configureActions(ActionsConfig $actions): void
    {
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Datagrid $datagrid
     */
    protected function configureDatagrid(Datagrid $datagrid): void
    {
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     */
    protected function configureQuery(QueryBuilder $queryBuilder): void
    {
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): Response
    {
        $adapter = $this->ormAdapterFactory->create($this->definition->entityClass, function (QueryBuilder $queryBuilder) {
            $this->configureQuery($queryBuilder);

            foreach ($this->definition->getExtensions() as $extension) {
                $extension->configureQuery($queryBuilder);
            }
        });
        $datagrid = $this->datagridFactory->create($adapter, [
            'crudDefinition' => $this->definition,
            'name' => $this->definition->entityName,
            'roleConstant' => $this->definition->getRoleConstant(),
        ]);
        $this->configureDatagrid($datagrid);

        foreach ($this->definition->getExtensions() as $extension) {
            $extension->configureDatagrid($datagrid);
        }

        return $this->render('@ShopsysAdministration/crud/list.html.twig', [
            'title' => $this->definition->getConfig()->getTitle(ActionType::LIST),
            'grid' => $datagrid->createView(),
            'topActions' => $this->getConfiguredActions(ActionType::LIST),
        ]);
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailAction(int $id): Response
    {
        return $this->render('@ShopsysAdministration/crud/detail.html.twig', [
            'title' => $this->definition->getConfig()->getTitle(ActionType::DETAIL),
            'topActions' => $this->getConfiguredActions(ActionType::DETAIL),
        ]);
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(int $id): Response
    {
        return $this->render('@ShopsysAdministration/crud/edit.html.twig', [
            'title' => $this->definition->getConfig()->getTitle(ActionType::EDIT),
            'topActions' => $this->getConfiguredActions(ActionType::EDIT),
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction(): Response
    {
        return $this->render('@ShopsysAdministration/crud/new.html.twig', [
            'title' => $this->definition->getConfig()->getTitle(ActionType::CREATE),
            'topActions' => $this->getConfiguredActions(ActionType::CREATE),
        ]);
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(int $id): Response
    {
        return $this->redirect($this->generateUrl('admin_default_dashboard'));
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType $actionType
     * @return \Shopsys\AdministrationBundle\Component\Action\AbstractAction[]
     */
    final protected function getConfiguredActions(ActionType $actionType): array
    {
        $actionsConfig = new ActionsConfig(static::class, $this->definition->getConfig()->getActions());

        $this->configureActions($actionsConfig);

        foreach ($this->definition->getExtensions() as $extension) {
            $extension->configureActions($actionsConfig);
        }

        return $actionsConfig->getActions($actionType);
    }
}
