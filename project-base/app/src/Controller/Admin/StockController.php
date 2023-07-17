<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Admin\StockFormType;
use App\Form\Admin\StockSettingsFormType;
use App\Model\Stock\Exception\StockNotFoundException;
use App\Model\Stock\Stock;
use App\Model\Stock\StockDataFactory;
use App\Model\Stock\StockFacade;
use App\Model\Stock\StockSettingsDataFacade;
use App\Model\Stock\StockSettingsDataFactory;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StockController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \App\Model\Stock\StockFacade $stockFacade
     * @param \App\Model\Stock\StockDataFactory $stockDataFactory
     * @param \App\Model\Stock\StockSettingsDataFacade $stockSettingsDataFacade
     * @param \App\Model\Stock\StockSettingsDataFactory $stockSettingsDataFactory
     */
    public function __construct(
        private GridFactory $gridFactory,
        private BreadcrumbOverrider $breadcrumbOverrider,
        private AdminDomainTabsFacade $adminDomainTabsFacade,
        private StockFacade $stockFacade,
        private StockDataFactory $stockDataFactory,
        private StockSettingsDataFacade $stockSettingsDataFacade,
        private StockSettingsDataFactory $stockSettingsDataFactory,
    ) {
    }

    /**
     * @Route("/stock/list/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request): Response
    {
        $grid = $this->getGrid();

        return $this->render('Admin/Content/Stock/list.html.twig', [
            'gridView' => $grid->createView(),
            'settingsForm' => $this->getStockSettingsForm()->createView(),
        ]);
    }

    /**
     * @Route("/stock/setting/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function settingsAction(Request $request): Response
    {
        return $this->render('Admin/Content/Stock/settings.html.twig', [
            'form' => $this->getStockSettingsForm()->createView(),
        ]);
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    private function getStockSettingsForm(): FormInterface
    {
        $stockSettingsData = $this->stockSettingsDataFactory->getForDomainId(
            $this->adminDomainTabsFacade->getSelectedDomainId(),
        );

        return $this->createForm(StockSettingsFormType::class, $stockSettingsData, [
            'action' => $this->generateUrl('admin_stock_savesettings'),
        ]);
    }

    /**
     * @Route("/stock/savesettings/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function saveSettingsAction(Request $request): RedirectResponse
    {
        $form = $this->getStockSettingsForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $stockSettingsData = $form->getData();

            $this->stockSettingsDataFacade->edit($stockSettingsData);

            $this
                ->addSuccessFlashTwig(
                    t(
                        'Warehouse setting %domainName% saved.',
                        [
                            '%domainName%' => $this->adminDomainTabsFacade->getSelectedDomainConfig()->getName(),
                        ],
                    ),
                );
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->redirectToRoute('admin_stock_list');
    }

    /**
     * @Route("/stock/new/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request): Response
    {
        $stockData = $this->stockDataFactory->create();

        $form = $this->createForm(StockFormType::class, $stockData, [
            'stock' => null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $stockData = $form->getData();

            $stock = $this->stockFacade->create($stockData);

            $this
                ->addSuccessFlashTwig(
                    t('Warehouse <strong><a href="{{ url }}">{{ name }}</a></strong> created'),
                    [
                        'name' => $stock->getName(),
                        'url' => $this->generateUrl('admin_stock_edit', ['id' => $stock->getId()]),
                    ],
                );

            return $this->redirectToRoute('admin_stock_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('Admin/Content/Stock/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/stock/edit/{id}", requirements={"id" = "\d+"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, int $id): Response
    {
        $stock = $this->stockFacade->getById($id);
        $stockData = $this->stockDataFactory->createFromStock($stock);

        $form = $this->createForm(StockFormType::class, $stockData, [
            'stock' => $stock,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->stockFacade->edit($id, $stockData);

            $this
                ->addSuccessFlashTwig(
                    t('Warehouse <strong><a href="{{ url }}">{{ name }}</a></strong> modified'),
                    [
                        'name' => $stock->getName(),
                        'url' => $this->generateUrl('admin_stock_edit', ['id' => $stock->getId()]),
                    ],
                );

            return $this->redirectToRoute('admin_stock_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(t('Editing warehouse - %name%', ['%name%' => $stock->getName()]));

        return $this->render('Admin/Content/Stock/edit.html.twig', [
            'form' => $form->createView(),
            'stock' => $stock,
        ]);
    }

    /**
     * @Route("/stock/setdefault/{id}", requirements={"id" = "\d+"})
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function setDefaultAction(int $id): Response
    {
        try {
            $stock = $this->stockFacade->getById($id);

            $this->stockFacade->changeDefaultStock($stock);

            $this->addSuccessFlashTwig(
                t('Warehouse <strong>{{ name }}</strong> was set as default.'),
                [
                    'name' => $stock->getName(),
                ],
            );
        } catch (StockNotFoundException $ex) {
            $this->addErrorFlash(t('Selected warehouse does not exist.'));
        }

        return $this->redirectToRoute('admin_stock_list');
    }

    /**
     * @Route("/stock/delete/{id}", requirements={"id" = "\d+"})
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(int $id): RedirectResponse
    {
        try {
            $stock = $this->stockFacade->getById($id);

            if ($stock->isDefault()) {
                $this->addErrorFlash('Cannot delete the default stock');

                return $this->redirectToRoute('admin_stock_list');
            }

            $this->stockFacade->delete($id);

            $this->addSuccessFlashTwig(
                t('Warehouse <strong>{{ name }}</strong> deleted'),
                [
                    'name' => $stock->getName(),
                ],
            );
        } catch (StockNotFoundException $ex) {
            $this->addErrorFlash(t('Selected warehouse does not exist.'));
        }

        return $this->redirectToRoute('admin_stock_list');
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    private function getGrid(): Grid
    {
        $queryBuilder = $this->stockFacade->getAllStockQueryBuilder();

        $dataSource = new QueryBuilderDataSource($queryBuilder, 's.id');

        $grid = $this->gridFactory->create('stockList', $dataSource);

        $grid->addColumn('name', 's.name', t('Name'));
        $grid->setDefaultOrder('s.position');

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_stock_edit', ['id' => 's.id']);
        $grid->addDeleteActionColumn('admin_stock_delete', ['id' => 's.id'])
            ->setConfirmMessage(t('Do you really want to remove this warehouse? By deleting this warehouse you will '
                . 'remove all stock quantities from products and association to stores. This step is irreversible!'));
        $grid->enableDragAndDrop(Stock::class);

        $grid->setTheme('Admin/Content/Stock/listGrid.html.twig');

        return $grid;
    }
}
