<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use ReflectionClass;
use RuntimeException;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Component\Config\Action\ActionsConfig;
use Shopsys\AdministrationBundle\Component\Config\Action\ActionsFactory;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Config\CrudConfigData;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\OrmAdapterFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Component\Datagrid\DatagridFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractCrudController extends AbstractController
{
    private ?CrudConfigData $config = null;

    private ?ActionsConfig $actions = null;

    #[Required]
    public ActionsFactory $actionsFactory;

    #[Required]
    public DatagridFactory $datagridFactory;

    #[Required]
    public OrmAdapterFactory $ormAdapterFactory;

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\CrudConfig $config
     * @return \Shopsys\AdministrationBundle\Component\Config\CrudConfig
     */
    protected function configure(CrudConfig $config): CrudConfig
    {
        return $config;
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\Action\ActionsConfig $actions
     * @return \Shopsys\AdministrationBundle\Component\Config\Action\ActionsConfig
     */
    protected function configureActions(ActionsConfig $actions): ActionsConfig
    {
        return $actions;
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Datagrid $datagrid
     * @return \Shopsys\AdministrationBundle\Component\Datagrid\Datagrid
     */
    protected function configureDatagrid(Datagrid $datagrid): Datagrid
    {
        return $datagrid;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): Response
    {
        $adapter = $this->ormAdapterFactory->create($this->getConfig()->getEntityClass());
        $datagrid = $this->datagridFactory->create($adapter);
        $this->configureDatagrid($datagrid);


        return $this->render('@ShopsysAdministration/crud/list.html.twig', [
            'title' => $this->getConfig()->getTitle(ActionType::LIST),
            'grid' => $datagrid->createView(),
            'topActions' => $this->actionsFactory->processActions($this->getConfiguredActions(ActionType::LIST)),
        ]);
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(int $id): Response
    {
        return $this->render('@ShopsysAdministration/crud/edit.html.twig', [
            'title' => $this->getConfig()->getTitle(ActionType::EDIT),
            'topActions' => $this->actionsFactory->processActions($this->getConfiguredActions(ActionType::EDIT)),
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction(): Response
    {
        return $this->render('@ShopsysAdministration/crud/new.html.twig', [
            'title' => $this->getConfig()->getTitle(ActionType::CREATE),
            'topActions' => $this->actionsFactory->processActions($this->getConfiguredActions(ActionType::CREATE)),
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(): Response
    {
        return $this->redirect($this->generateUrl('admin_default_dashboard'));
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType $actionType
     * @return \Shopsys\AdministrationBundle\Component\Config\Action\Builder\AbstractAction[]
     */
    private function getConfiguredActions(ActionType $actionType): array
    {
        if ($this->actions === null) {
            $this->actions = $this->configureActions(new ActionsConfig(static::class, $this->getConfig()->getActions()));
        }

        return $this->actions->getActions($actionType);
    }

    /**
     * @return \Shopsys\AdministrationBundle\Component\Config\CrudConfigData
     */
    final public function getConfig(): CrudConfigData
    {
        if ($this->config === null) {
            $reflectionClass = new ReflectionClass($this);
            $attributes = $reflectionClass->getAttributes(CrudController::class);

            if (count($attributes) === 0) {
                throw new RuntimeException(sprintf('Class %s must have @%s attribute.', $reflectionClass->getName(), CrudController::class));
            }

            $entityClass = $attributes[0]->newInstance()->entityClass;
            $this->config = $this->configure(new CrudConfig($entityClass))->getConfig();
        }

        return $this->config;
    }
}
