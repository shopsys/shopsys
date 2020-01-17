<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Admin\Product\Series\ProductSeriesFormType;
use App\Model\Product\Series\ProductSeriesDataFactoryInterface;
use App\Model\Product\Series\ProductSeriesFacadeInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Symfony\Component\HttpFoundation\Request;

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
     * @param \App\Model\Product\Series\ProductSeriesDataFactoryInterface $productSeriesDataFactory
     * @param \App\Model\Product\Series\ProductSeriesFacadeInterface $productSeriesFacade
     */
    public function __construct(
        ProductSeriesDataFactoryInterface $productSeriesDataFactory,
        ProductSeriesFacadeInterface $productSeriesFacade
    ) {
        $this->productSeriesDataFactory = $productSeriesDataFactory;
        $this->productSeriesFacade = $productSeriesFacade;
    }

    /**
     * @Route("/product-series/list", name="admin_productseries_list")
     */
    public function listAction()
    {
        $grid = 'ahoj';

        return $this->render(
            'Admin/Content/ProductSeries/list.html.twig',
            ['gridView' => $grid]
        );
    }

    /**
     * @Route("/product-series/new", name="admin_productseries_new")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function newAction(Request $request)
    {
        $productSeriesData = $this->productSeriesDataFactory->create();

        $form = $this->createForm(ProductSeriesFormType::class, $productSeriesData, ['productSeries' => null]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var \App\Model\Product\Series\ProductSeriesData $productSeriesData
             */
            $productSeriesData = $form->getData();
            d($productSeriesData);
            $productSeries = $this->productSeriesFacade->create($productSeriesData);
            d($productSeries);
            $this->getFlashMessageSender()
                ->addSuccessFlashTwig(
                    t('Produktový program <strong><a href="{{ url }}">{{ productSeries.name }}</a></strong> je úspěšně vytvořen'),
                    [
                        'productSeries' => $productSeries,
                        'url' => $this->generateUrl('admin_productseries_edit', ['id' => $productSeries->getId()]),
                    ]
                );

//            return $this->redirectToRoute('admin_productseries_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->getFlashMessageSender()->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
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
     * @param mixed $id
     */
    public function editAction(Request $request, int $id)
    {

        $productSeries = $this->productSeriesFacade->getById($id);

        $productSeriesData = $this->productSeriesDataFactory->createFromProductSeries($productSeries);

        $form = $this->createForm(ProductSeriesFormType::class, $productSeriesData, ['productSeries' => $productSeries]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var \App\Model\Product\Series\ProductSeriesData $productSeriesData
             */
            $productSeriesData = $form->getData();
            d($productSeriesData);
            $productSeries = $this->productSeriesFacade->edit($id, $productSeriesData);
            d($productSeries);
            $this->getFlashMessageSender()
                ->addSuccessFlashTwig(
                    t('Produktový program <strong><a href="{{ url }}">{{ productSeries.name }}</a></strong> je úspěšně upraven'),
                    [
                        'productSeries' => $productSeries,
                        'url' => $this->generateUrl('admin_productseries_edit', ['id' => $productSeries->getId()]),
                    ]
                );

//            return $this->redirectToRoute('admin_productseries_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->getFlashMessageSender()->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('Admin/Content/ProductSeries/edit.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
