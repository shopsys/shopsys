<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Shopsys\AdministrationBundle\Component\Crud\CrudControllerRegistry;
use Shopsys\AdministrationBundle\Component\Crud\CrudRegistryItem;
use Shopsys\AdministrationBundle\Component\Crud\Helper\CrudTransformationHelper;
use Shopsys\AdministrationBundle\Component\Search\AdvancedSearchFormFactory;
use Shopsys\AdministrationBundle\Component\Search\SearchConfigFactory;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleIdentifierHelper;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Symfony\Component\DependencyInjection\Attribute\TaggedLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_ADMINISTRATOR)]
class CrudSearchController extends AdminBaseController
{
    public function __construct(
        protected readonly CrudControllerRegistry $crudControllerRegistry,
        protected readonly SearchConfigFactory $searchConfigFactory,
        protected readonly AdvancedSearchFormFactory $advancedSearchFormFactory,
        #[TaggedLocator('shopsys.admin.crud_controllers')]
        protected readonly ServiceLocator $crudControllers,
    ) {
    }

    /**
     * Renders one advanced search rule row, used by JS to swap the row when the administrator changes the subject.
     */
    #[Route(path: '/crud-search/rule-form/{crudControllerName}', name: 'admin_crud_search_rule_form', methods: ['post'])]
    #[CanView]
    public function ruleFormAction(Request $request, string $crudControllerName): Response
    {
        $registryItem = $this->getRegistryItemByRouteName($crudControllerName);

        if (!$this->isGranted(RoleIdentifierHelper::getIdentifierWithPermission($registryItem->getRoleConstant(), Permission::VIEW))) {
            throw $this->createAccessDeniedException();
        }

        /** @var \Shopsys\AdministrationBundle\Controller\AbstractCrudController $crudController */
        $crudController = $this->crudControllers->get($registryItem->controllerClass);
        $definition = $this->crudControllerRegistry->getDefinition($registryItem->controllerClass);
        // outside its own request, the CRUD controller does not go through CrudControllerInitializer
        $crudController->setDefinition($definition);
        $searchConfig = $this->searchConfigFactory->create($crudController, $definition);

        $filterName = $request->request->getString('filterName');

        if ($searchConfig->getFilter($filterName) === null) {
            throw $this->createNotFoundException(sprintf('Advanced search filter "%s" not found.', $filterName));
        }

        $ruleFormView = $this->advancedSearchFormFactory->createRuleFormView(
            $searchConfig,
            $filterName,
            $request->request->getString('newIndex'),
        );

        return $this->render('@ShopsysAdministration/crud/search/_rule.html.twig', [
            'ruleForm' => $ruleFormView,
        ]);
    }

    private function getRegistryItemByRouteName(string $crudControllerName): CrudRegistryItem
    {
        foreach ($this->crudControllerRegistry->getAll() as $registryItem) {
            if (CrudTransformationHelper::transformToRouteName($registryItem->controllerName) === $crudControllerName) {
                return $registryItem;
            }
        }

        throw $this->createNotFoundException(sprintf('CRUD controller "%s" not found.', $crudControllerName));
    }
}
