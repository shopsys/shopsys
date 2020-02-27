<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Admin\Product\Series\ProductSeriesCategoryFormType;
use App\Model\Product\Series\Category\Grid\ProductSeriesCategoryGridFactory;
use App\Model\Product\Series\Category\ProductSeriesCategoryDataFactory;
use App\Model\Product\Series\Category\ProductSeriesCategoryFacade;
use App\Model\Product\Series\Exception\ProductSeriesNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductSeriesCategoryController extends AdminBaseController
{
    /**
     * @var \App\Model\Product\Series\Category\Grid\ProductSeriesCategoryGridFactory
     */
    private $productSeriesCategoryGridFactory;

    /**
     * @var \App\Model\Product\Series\Category\ProductSeriesCategoryFacade
     */
    private $productSeriesCategoryFacade;

    /**
     * @var \App\Model\Product\Series\Category\ProductSeriesCategoryDataFactory
     */
    private $productSeriesCategoryDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    private $localization;

    /**
     * @var \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider
     */
    private $breadcrumbOverrider;

    /**
     * @param \App\Model\Product\Series\Category\Grid\ProductSeriesCategoryGridFactory $productSeriesCategoryGridFactory
     * @param \App\Model\Product\Series\Category\ProductSeriesCategoryFacade $productSeriesCategoryFacade
     * @param \App\Model\Product\Series\Category\ProductSeriesCategoryDataFactory $productSeriesCategoryDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     */
    public function __construct(
        ProductSeriesCategoryGridFactory $productSeriesCategoryGridFactory,
        ProductSeriesCategoryFacade $productSeriesCategoryFacade,
        ProductSeriesCategoryDataFactory $productSeriesCategoryDataFactory,
        Localization $localization,
        BreadcrumbOverrider $breadcrumbOverrider
    ) {
        $this->productSeriesCategoryGridFactory = $productSeriesCategoryGridFactory;
        $this->productSeriesCategoryFacade = $productSeriesCategoryFacade;
        $this->productSeriesCategoryDataFactory = $productSeriesCategoryDataFactory;
        $this->localization = $localization;
        $this->breadcrumbOverrider = $breadcrumbOverrider;
    }

    /**
     * @Route("/product-series-category/list", name="admin_productseriescategory_list")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): Response
    {
        $grid = $this->productSeriesCategoryGridFactory->create();

        return $this->render(
            'Admin/Content/ProductSeriesCategory/list.html.twig',
            [
                'gridView' => $grid->createView(),
            ]
        );
    }

    /**
     * @Route("/product-series-category/delete/{id}", requirements={"id" = "\d+"}, name="admin_productseriescategory_delete")
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(int $id): RedirectResponse
    {
        try {
            $productSeriesCategory = $this->productSeriesCategoryFacade->getById($id);
            $fullName = $productSeriesCategory->getName($this->localization->getLocale());

            $this->productSeriesCategoryFacade->delete($productSeriesCategory);

            $this->getFlashMessageSender()->addSuccessFlashTwig(
                t('Kategorie produktového programu <strong>{{ name }}</strong> je smazána'),
                [
                    'name' => $fullName,
                ]
            );
        } catch (ProductSeriesNotFoundException $ex) {
            $this->getFlashMessageSender()->addErrorFlash(t('Vybraná kategorie produktového programu již neexistuje.'));
        }

        return $this->redirectToRoute('admin_productseries_list');
    }

    /**
     * @Route("/product-series-category/new", name="admin_productseriescategory_new")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request): Response
    {
        $productSeriesData = $this->productSeriesCategoryDataFactory->create();

        $form = $this->createForm(ProductSeriesCategoryFormType::class, $productSeriesData, ['productSeriesCategory' => null]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productSeriesCategory = $this->productSeriesCategoryFacade->create($form->getData());

            $this->getFlashMessageSender()
                ->addSuccessFlashTwig(
                    t('Produktový program <strong><a href="{{ url }}">{{ productSeriesCategoryName }}</a></strong> je úspěšně vytvořen'),
                    [
                        'productSeriesCategoryName' => $productSeriesCategory->getName($this->localization->getLocale()),
                        'url' => $this->generateUrl('admin_productseriescategory_edit', ['id' => $productSeriesCategory->getId()]),
                    ]
                );

            return $this->redirectToRoute('admin_productseriescategory_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->getFlashMessageSender()->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render(
            'Admin/Content/ProductSeriesCategory/new.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/product-series-category/edit/{id}", requirements={"id" = "\d+"}, name="admin_productseriescategory_edit")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, int $id): Response
    {
        $productSeriesCategory = $this->productSeriesCategoryFacade->getById($id);

        $productSeriesCategoryData = $this->productSeriesCategoryDataFactory->createFromProductSeriesCategory($productSeriesCategory);

        $form = $this->createForm(ProductSeriesCategoryFormType::class, $productSeriesCategoryData, ['productSeriesCategory' => $productSeriesCategory]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productSeriesCategory = $this->productSeriesCategoryFacade->edit($id, $form->getData());

            $this->getFlashMessageSender()
                ->addSuccessFlashTwig(
                    t('Kategorie produktového programu <strong><a href="{{ url }}">{{ productSeriesCategory.name }}</a></strong> je úspěšně upravena'),
                    [
                        'productSeriesCategory' => $productSeriesCategory,
                        'url' => $this->generateUrl('admin_productseriescategory_edit', ['id' => $productSeriesCategory->getId()]),
                    ]
                );
            return $this->redirectToRoute('admin_productseriescategory_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->getFlashMessageSender()->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(t('Úprava kategorie produktového programu - %name%', ['%name%' => $productSeriesCategory->getName($this->localization->getLocale())]));

        return $this->render(
            'Admin/Content/ProductSeriesCategory/edit.html.twig',
            [
                'productSeriesCategory' => $productSeriesCategory,
                'form' => $form->createView(),
            ]
        );
    }
}
