<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Override;
use Shopsys\AdministrationBundle\Component\Action\Action;
use Shopsys\AdministrationBundle\Component\Action\RowAction;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Component\Config\ActionsConfig;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Config\CrudListDomainControl;
use Shopsys\AdministrationBundle\Component\Crud\Form\CrudFormConfigurator;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Model\Store\StoreCrudHandler;
use Shopsys\FrameworkBundle\Component\AddressCoordinates\Exception\GoogleAddressCoordinatesException;
use Shopsys\FrameworkBundle\Component\AddressCoordinates\GoogleAddressCoordinatesFacade;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Store\StoreFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\SideMenuBuilder;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

#[CrudController(Store::class)]
#[ForRole(AdminRoleConstant::ROLE_STORE)]
class StoreController extends AbstractCrudController
{
    public function __construct(
        protected readonly StoreFacade $storeFacade,
        protected readonly GoogleAddressCoordinatesFacade $addressCoordinatesFacade,
    ) {
    }

    #[Override]
    public function configure(CrudConfig $config): void
    {
        $config
            ->setMenuSection(
                SideMenuBuilder::ROOT_SETTING,
                SideMenuBuilder::SECTION_LISTS,
                ['after' => SideMenuBuilder::LIST_PARAMETER_VALUE],
            )
            ->setListDomainControl(CrudListDomainControl::SWITCHER)
            ->registerHandler(StoreCrudHandler::class);
    }

    #[Override]
    protected function configureActions(ActionsConfig $actions): void
    {
        $actions->add(
            ActionType::EDIT,
            Action::create('setDefault', t('Set as default'))
                ->setAttribute('class', 'btn-secondary', true)
                ->displayIf(fn (Store $store): bool => !$store->isDefault())
                ->linkToRoute('admin_crud_store_set_default', fn (Store $store): array => ['id' => $store->getId()]),
        );
    }

    #[Override]
    protected function configureDatagrid(Datagrid $datagrid): void
    {
        $datagrid
            ->add('isDefault', [
                'visible' => false,
            ])
            ->add('name', [
                'label' => t('Name'),
                'template' => '@ShopsysAdministration/content/store/grid/name.html.twig',
            ]);

        $datagrid->enableDragAndDrop('position');

        $datagrid->actions()->update(
            'delete',
            fn (RowAction $rowAction) => $rowAction
                ->setConfirmMessage(t('Do you really want to remove this store? This step is irreversible!'))
                ->addCallback(function (array $row, RowAction $action): void {
                    if ($row['isDefault'] === true) {
                        $action->disableWithMessage(t('Default store cannot be removed'));
                    }
                }),
        );
    }

    #[Override]
    protected function configureForm(CrudFormConfigurator $formConfigurator, ?object $entity = null): void
    {
        $formConfigurator->useFormType(StoreFormType::class, [
            'store' => $entity,
        ]);
    }

    #[Route(path: '/store/set-default/{id}', name: 'admin_crud_store_set_default', requirements: ['id' => '\d+'])]
    #[CanEdit]
    #[CsrfProtection]
    public function setDefaultAction(int $id): Response
    {
        /** @var \Shopsys\AdministrationBundle\Model\Store\StoreCrudHandler $handler */
        $handler = $this->definition->getHandlerForAction(ActionType::EDIT);
        /** @var \Shopsys\FrameworkBundle\Model\Store\Store $store */
        $store = $handler->getById($id);
        $this->storeFacade->changeDefaultStore($store);

        $this->addSuccessFlashTwig(
            t('Store <strong>{{ name }}</strong> was set as default.'),
            [
                'name' => $store->getName(),
            ],
        );

        return $this->redirectToRoute('admin_crud_store_list');
    }

    #[Route(
        path: '/store/load-coordinates',
        name: 'admin_crud_store_load_coordinates',
        methods: ['post'],
        condition: 'request.isXmlHttpRequest()',
    )]
    #[CanView(methods: [HttpMethod::POST])]
    public function loadCoordinatesAction(Request $request): JsonResponse
    {
        try {
            $addressCoordinatesData = $this->addressCoordinatesFacade->getCoordinatesByStructuredAddress(
                $request->request->getString('street'),
                $request->request->getString('city'),
                $request->request->getString('countryCode'),
                $request->request->getString('postcode'),
            );
        } catch (GoogleAddressCoordinatesException|ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface) {
            return new JsonResponse();
        }

        if ($addressCoordinatesData === null) {
            return new JsonResponse();
        }

        return new JsonResponse([
            'latitude' => $addressCoordinatesData->latitude,
            'longitude' => $addressCoordinatesData->longitude,
        ]);
    }
}
