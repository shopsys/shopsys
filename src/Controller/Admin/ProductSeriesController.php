<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Component\Form\FormBuilderHelper;
use App\Form\Admin\Product\Series\ProductSeriesFormType;
use App\Model\Product\Series\Exception\ProductSeriesNotFoundException;
use App\Model\Product\Series\Grid\ProductSeriesGridFactory;
use App\Model\Product\Series\ProductSeries;
use App\Model\Product\Series\ProductSeriesDataFactoryInterface;
use App\Model\Product\Series\ProductSeriesFacadeInterface;
use App\Model\Product\Series\ProductSeriesProductRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductSeriesController extends AdminBaseController
{
    /**
     * @var \App\Model\Product\Series\ProductSeriesDataFactoryInterface
     */
    private $productSeriesDataFactory;

    /**
     * @var \App\Model\Product\Series\ProductSeriesFacadeInterface
     */
    private $productSeriesFacade;

    /**
     * @var \App\Model\Product\Series\Grid\ProductSeriesGridFactory
     */
    private $productSeriesGridFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider
     */
    private $breadcrumbOverrider;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    private $localization;

    /**
     * @var \App\Component\Form\FormBuilderHelper
     */
    private $formBuilderHelper;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\GridFactory
     */
    private $gridFactory;

    /**
     * @var \App\Model\Product\Series\ProductSeriesProductRepository
     */
    private $productSeriesProductRepository;

    /**
     * @param \App\Model\Product\Series\ProductSeriesDataFactoryInterface $productSeriesDataFactory
     * @param \App\Model\Product\Series\ProductSeriesFacadeInterface $productSeriesFacade
     * @param \App\Model\Product\Series\Grid\ProductSeriesGridFactory $productSeriesGridFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param \App\Component\Form\FormBuilderHelper $formBuilderHelper
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \App\Model\Product\Series\ProductSeriesProductRepository $productSeriesProductRepository
     */
    public function __construct(
        ProductSeriesDataFactoryInterface $productSeriesDataFactory,
        ProductSeriesFacadeInterface $productSeriesFacade,
        ProductSeriesGridFactory $productSeriesGridFactory,
        Domain $domain,
        BreadcrumbOverrider $breadcrumbOverrider,
        Localization $localization,
        FormBuilderHelper $formBuilderHelper,
        GridFactory $gridFactory,
        ProductSeriesProductRepository $productSeriesProductRepository
    ) {
        $this->productSeriesDataFactory = $productSeriesDataFactory;
        $this->productSeriesFacade = $productSeriesFacade;
        $this->productSeriesGridFactory = $productSeriesGridFactory;
        $this->domain = $domain;
        $this->breadcrumbOverrider = $breadcrumbOverrider;
        $this->localization = $localization;
        $this->formBuilderHelper = $formBuilderHelper;
        $this->gridFactory = $gridFactory;
        $this->productSeriesProductRepository = $productSeriesProductRepository;
    }

    /**
     * @Route("/product-series/list", name="admin_productseries_list")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): Response
    {
        $grid = $this->productSeriesGridFactory->create();
        $domains = $this->domain->getAll();

        return $this->render(
            'Admin/Content/ProductSeries/list.html.twig',
            [
                'gridView' => $grid->createView(),
                'domains' => $domains,
                'hasFormDisabledFields' => $this->formBuilderHelper->hasFormDisabledFields(),
            ]
        );
    }

    /**
     * @Route("/product-series/delete/{id}", requirements={"id" = "\d+"}, name="admin_productseries_delete")
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(int $id): RedirectResponse
    {
        try {
            $productSeries = $this->productSeriesFacade->getById($id);
            $fullName = $productSeries->getName($this->localization->getLocale());

            $this->productSeriesFacade->delete($productSeries);

            $this->addSuccessFlashTwig(
                t('Produktový program <strong>{{ name }}</strong> je smazán'),
                [
                    'name' => $fullName,
                ]
            );
        } catch (ProductSeriesNotFoundException $ex) {
            $this->addErrorFlash(t('Vybraný produktový program neexistuje.'));
        }

        return $this->redirectToRoute('admin_productseries_list');
    }

    /**
     * @Route("/product-series/new", name="admin_productseries_new")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request): Response
    {
        $productSeriesData = $this->productSeriesDataFactory->create();

        $form = $this->createForm(ProductSeriesFormType::class, $productSeriesData, ['productSeries' => null]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productSeries = $this->productSeriesFacade->create($form->getData());

            $this
                ->addSuccessFlashTwig(
                    t('Produktový program <strong><a href="{{ url }}">{{ productSeries.name }}</a></strong> je úspěšně vytvořen'),
                    [
                        'productSeries' => $productSeries,
                        'url' => $this->generateUrl('admin_productseries_edit', ['id' => $productSeries->getId()]),
                    ]
                );

            return $this->redirectToRoute('admin_productseries_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render(
            'Admin/Content/ProductSeries/new.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/product-series/edit/{id}", requirements={"id" = "\d+"}, name="admin_productseries_edit")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, int $id): Response
    {
        $productSeries = $this->productSeriesFacade->getById($id);

        $productSeriesData = $this->productSeriesDataFactory->createFromProductSeries($productSeries);

        $form = $this->createForm(ProductSeriesFormType::class, $productSeriesData, ['productSeries' => $productSeries]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productSeries = $this->productSeriesFacade->edit($id, $form->getData());

            $this
                ->addSuccessFlashTwig(
                    t('Produktový program <strong><a href="{{ url }}">{{ productSeries.name }}</a></strong> je úspěšně upraven'),
                    [
                        'productSeries' => $productSeries,
                        'url' => $this->generateUrl('admin_productseries_edit', ['id' => $productSeries->getId()]),
                    ]
                );
            return $this->redirectToRoute('admin_productseries_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $productsGrid = $this->getProductsGrid($productSeries);

        $this->breadcrumbOverrider->overrideLastItem(t('Úprava produktového programu - %name%', ['%name%' => $productSeries->getName($this->domain->getLocale())]));

        return $this->render(
            'Admin/Content/ProductSeries/edit.html.twig',
            [
                'productSeries' => $productSeries,
                'productsGrid' => $productsGrid->createView(),
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @param \App\Model\Product\Series\ProductSeries $productSeries
     * @throws \Shopsys\FrameworkBundle\Component\Grid\Exception\DuplicateColumnIdException
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    private function getProductsGrid(ProductSeries $productSeries): Grid
    {
        $queryBuilder = $this->productSeriesProductRepository->getProductSeriesProductsQueryBuilderByProductSeries($productSeries);
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'p.id');

        $grid = $this->gridFactory->create('productGrid', $dataSource);
        $grid->addColumn('name', 'p.name', t('Name'));
        $grid->addEditActionColumn('admin_product_edit', ['id' => 'p.id']);
        $grid->setTheme('Admin/Content/ProductSeries/Product/listGrid.html.twig');

        return $grid;
    }
}
