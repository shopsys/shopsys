<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\AddressCoordinates\Exception\GoogleAddressCoordinatesException;
use Shopsys\FrameworkBundle\Component\AddressCoordinates\GoogleAddressCoordinatesFacade;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanCreate;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Store\StoreFormType;
use Shopsys\FrameworkBundle\Model\Store\Exception\StoreNotFoundException;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\FrameworkBundle\Model\Store\StoreDataFactory;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

#[ForRole(AdminRoleConstant::ROLE_STORE)]
class StoreController extends AdminBaseController
{
    public function __construct(
        protected readonly StoreFacade $storeFacade,
        protected readonly StoreDataFactory $storeDataFactory,
        protected readonly GridFactory $gridFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
        protected readonly GoogleAddressCoordinatesFacade $addressCoordinatesFacade,
    ) {
    }

    #[Route(path: '/store/list/')]
    #[CanView]
    public function listAction(): Response
    {
        return $this->render('@ShopsysAdministration/content/store/list.html.twig', [
            'gridView' => $this->getGrid()->createView(),
        ]);
    }

    protected function getGrid(): Grid
    {
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $queryBuilder = $this->storeFacade->getStoresByDomainIdQueryBuilder($domainId);

        $dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 's.id');

        $grid = $this->gridFactory->create('storeList', $dataSource, AdminRoleConstant::ROLE_STORE);

        $grid->addColumn('name', 's.name', t('Name'));
        $grid->setDefaultOrder('s.position');

        $grid->addEditActionColumn('admin_store_edit', ['id' => 's.id']);
        $grid->addDeleteActionColumn('admin_store_delete', ['id' => 's.id'])
            ->setConfirmMessage(t('Do you really want to remove this store? This step is irreversible!'));
        $grid->enableDragAndDrop(Store::class);

        $grid->setTheme('@ShopsysAdministration/content/store/listGrid.html.twig');

        return $grid;
    }

    #[Route(path: '/store/new/')]
    #[CanCreate]
    public function newAction(Request $request): Response
    {
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $storeData = $this->storeDataFactory->createForDomain($domainId);

        $form = $this->createForm(StoreFormType::class, $storeData, [
            'store' => null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $store = $this->storeFacade->create($storeData);

            $this->addSuccessFlashTwig(
                t('Store <strong><a href="{{ url }}">{{ name }}</a></strong> created'),
                [
                    'name' => $store->getName(),
                    'url' => $this->generateUrl('admin_store_edit', ['id' => $store->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_store_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAdministration/content/store/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/store/edit/{id}', requirements: ['id' => '\d+'])]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function editAction(Request $request, int $id): Response
    {
        $store = $this->storeFacade->getById($id);
        $storeData = $this->storeDataFactory->createFromStore($store);

        $form = $this->createForm(StoreFormType::class, $storeData, [
            'store' => $store,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $store = $this->storeFacade->edit($id, $storeData);

            $this->addSuccessFlashTwig(
                t('Store <strong><a href="{{ url }}">{{ name }}</a></strong> edited'),
                [
                    'name' => $store->getName(),
                    'url' => $this->generateUrl('admin_store_edit', ['id' => $store->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_store_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAdministration/content/store/edit.html.twig', [
            'form' => $form->createView(),
            'store' => $store,
        ]);
    }

    #[Route(
        path: '/store/load-coordinates',
        name: 'admin_store_loadcoordinates',
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

    #[Route(path: '/store/delete/{id}', name: 'admin_store_delete', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAction(int $id): Response
    {
        try {
            $store = $this->storeFacade->getById($id);

            if ($store->isDefault()) {
                $this->addErrorFlash('Cannot delete the default store');

                return $this->redirectToRoute('admin_store_list');
            }

            $this->storeFacade->delete($id);

            $this->addSuccessFlashTwig(
                t('Store <strong>{{ name }}</strong> was deleted'),
                [
                    'name' => $store->getName(),
                ],
            );
        } catch (StoreNotFoundException $exception) {
            $this->addErrorFlash(t('Store does not exist'));
        }

        return $this->redirectToRoute('admin_store_list');
    }

    #[Route(path: '/store/setdefault/{id}', requirements: ['id' => '\d+'])]
    #[CanEdit]
    #[CsrfProtection]
    public function setDefaultAction(int $id): Response
    {
        try {
            $store = $this->storeFacade->getById($id);

            $this->storeFacade->changeDefaultStore($store);

            $this->addSuccessFlashTwig(
                t('Store <strong>{{ name }}</strong> was set as default.'),
                [
                    'name' => $store->getName(),
                ],
            );
        } catch (StoreNotFoundException $exception) {
            $this->addErrorFlash(t('Store does not exist'));
        }

        return $this->redirectToRoute('admin_store_list');
    }
}
