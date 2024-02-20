<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Form\Admin\Store\StoreFormType;
use Shopsys\FrameworkBundle\Model\Store\Exception\StoreNotFoundException;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\FrameworkBundle\Model\Store\StoreDataFactory;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StoreController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreFacade $storeFacade
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreDataFactory $storeDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(
        protected readonly StoreFacade $storeFacade,
        protected readonly StoreDataFactory $storeDataFactory,
        protected readonly GridFactory $gridFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    /**
     * @Route("/store/list/")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): Response
    {
        return $this->render('@ShopsysFramework/Admin/Content/Store/list.html.twig', [
            'gridView' => $this->getGrid()->createView(),
        ]);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    protected function getGrid(): Grid
    {
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $queryBuilder = $this->storeFacade->getStoresByDomainIdQueryBuilder($domainId);

        $dataSource = new QueryBuilderDataSource($queryBuilder, 's.id');

        $grid = $this->gridFactory->create('storeList', $dataSource);

        $grid->addColumn('name', 's.name', t('Name'));
        $grid->setDefaultOrder('s.position');

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_store_edit', ['id' => 's.id']);
        $grid->addDeleteActionColumn('admin_store_delete', ['id' => 's.id'])
            ->setConfirmMessage(t('Do you really want to remove this store? This step is irreversible!'));
        $grid->enableDragAndDrop(Store::class);

        $grid->setTheme('@ShopsysFramework/Admin/Content/Store/listGrid.html.twig');

        return $grid;
    }

    /**
     * @Route("/store/new/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
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

        return $this->render('@ShopsysFramework/Admin/Content/Store/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/store/edit/{id}", requirements={"id" = "\d+"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
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

        return $this->render('@ShopsysFramework/Admin/Content/Store/edit.html.twig', [
            'form' => $form->createView(),
            'store' => $store,
        ]);
    }

    /**
     * @Route("/store/delete/{id}", requirements={"id" = "\d+"}, name="admin_store_delete")
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
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

    /**
     * @Route("/store/setdefault/{id}", requirements={"id" = "\d+"})
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
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
