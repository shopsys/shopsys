<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

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
use Shopsys\FrameworkBundle\Form\Admin\Stock\StockFormType;
use Shopsys\FrameworkBundle\Form\Admin\Stock\StockSettingsFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Stock\Exception\StockNotFoundException;
use Shopsys\FrameworkBundle\Model\Stock\Stock;
use Shopsys\FrameworkBundle\Model\Stock\StockDataFactory;
use Shopsys\FrameworkBundle\Model\Stock\StockFacade;
use Shopsys\FrameworkBundle\Model\Stock\StockSettingsDataFacade;
use Shopsys\FrameworkBundle\Model\Stock\StockSettingsDataFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_STOCK)]
class StockController extends AdminBaseController
{
    public function __construct(
        protected readonly GridFactory $gridFactory,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly StockFacade $stockFacade,
        protected readonly StockDataFactory $stockDataFactory,
        protected readonly StockSettingsDataFacade $stockSettingsDataFacade,
        protected readonly StockSettingsDataFactory $stockSettingsDataFactory,
        protected readonly QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
    ) {
    }

    #[Route(path: '/stock/list/')]
    #[CanView]
    public function listAction(): Response
    {
        $grid = $this->getGrid();

        return $this->render('@ShopsysAdministration/content/stock/list.html.twig', [
            'gridView' => $grid->createView(),
            'settingsForm' => $this->getStockSettingsForm()->createView(),
        ]);
    }

    #[Route(path: '/stock/setting/')]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function settingsAction(): Response
    {
        return $this->render('@ShopsysAdministration/content/stock/settings.html.twig', [
            'form' => $this->getStockSettingsForm()->createView(),
        ]);
    }

    protected function getStockSettingsForm(): FormInterface
    {
        $stockSettingsData = $this->stockSettingsDataFactory->getForDomainId(
            $this->adminDomainTabsFacade->getSelectedDomainId(),
        );

        return $this->createForm(StockSettingsFormType::class, $stockSettingsData, [
            'action' => $this->generateUrl('admin_stock_savesettings'),
        ]);
    }

    #[Route(path: '/stock/savesettings/')]
    #[CanEdit]
    public function saveSettingsAction(Request $request): RedirectResponse
    {
        $form = $this->getStockSettingsForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $stockSettingsData = $form->getData();

            $this->stockSettingsDataFacade->edit(
                $stockSettingsData,
                $this->adminDomainTabsFacade->getSelectedDomainConfig(),
            );

            $this
                ->addSuccessFlashTwig(
                    t(
                        'Setting for domain "%domainName%" was saved.',
                        [
                            '%domainName%' => $this->adminDomainTabsFacade->getSelectedDomainConfig()->getName(),
                        ],
                    ),
                );
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->redirectToRoute('admin_stock_settings');
    }

    #[Route(path: '/stock/new/')]
    #[CanCreate]
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

        return $this->render('@ShopsysAdministration/content/stock/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/stock/edit/{id}', requirements: ['id' => '\d+'])]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
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

        return $this->render('@ShopsysAdministration/content/stock/edit.html.twig', [
            'form' => $form->createView(),
            'stock' => $stock,
        ]);
    }

    #[Route(path: '/stock/setdefault/{id}', requirements: ['id' => '\d+'])]
    #[CanEdit]
    #[CsrfProtection]
    public function setDefaultAction(int $id): Response
    {
        try {
            $stock = $this->stockFacade->getById($id);

            $this->stockFacade->changeDefaultStock($stock);

            $this->addSuccessFlashTwig(
                t('Warehouse <strong><a href="{{ url }}">{{ name }}</a></strong> was set as default.'),
                [
                    'name' => $stock->getName(),
                    'url' => $this->generateUrl('admin_stock_edit', ['id' => $stock->getId()]),
                ],
            );
        } catch (StockNotFoundException) {
            $this->addErrorFlash(t('Selected warehouse does not exist.'));
        }

        return $this->redirectToRoute('admin_stock_list');
    }

    #[Route(path: '/stock/delete/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
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
        } catch (StockNotFoundException) {
            $this->addErrorFlash(t('Selected warehouse does not exist.'));
        }

        return $this->redirectToRoute('admin_stock_list');
    }

    protected function getGrid(): Grid
    {
        $queryBuilder = $this->stockFacade->getAllStockQueryBuilder();

        $dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 's.id');

        $grid = $this->gridFactory->create('stockList', $dataSource, AdminRoleConstant::ROLE_STOCK);

        $grid->addColumn('name', 's.name', t('Name'));
        $grid->setDefaultOrder('s.position');

        $grid->addEditActionColumn('admin_stock_edit', ['id' => 's.id']);
        $grid->addDeleteActionColumn('admin_stock_delete', ['id' => 's.id'])
            ->setConfirmMessage(t('Do you really want to remove this warehouse? By deleting this warehouse you will '
                . 'remove all stock quantities from products and association to stores. This step is irreversible!'));
        $grid->enableDragAndDrop(Stock::class);

        $grid->setTheme('@ShopsysAdministration/content/stock/listGrid.html.twig');

        return $grid;
    }
}
