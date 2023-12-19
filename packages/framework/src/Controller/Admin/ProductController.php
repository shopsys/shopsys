<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSource;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Form\Admin\Product\ProductFormType;
use Shopsys\FrameworkBundle\Form\Admin\Product\ProductMassActionFormType;
use Shopsys\FrameworkBundle\Form\Admin\Product\VariantFormType;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchProductFacade;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Exception\VariantException;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListAdminFacade;
use Shopsys\FrameworkBundle\Model\Product\MassAction\ProductMassActionFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductData;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductVariantFacade;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade;
use Shopsys\FrameworkBundle\Twig\ProductExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\MassAction\ProductMassActionFacade $productMassActionFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface $productDataFactory
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade $administratorGridFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Listing\ProductListAdminFacade $productListAdminFacade
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchProductFacade $advancedSearchProductFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVariantFacade $productVariantFacade
     * @param \Shopsys\FrameworkBundle\Twig\ProductExtension $productExtension
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade $unitFacade
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade $availabilityFacade
     */
    public function __construct(
        protected readonly ProductMassActionFacade $productMassActionFacade,
        protected readonly GridFactory $gridFactory,
        protected readonly ProductFacade $productFacade,
        protected readonly ProductDataFactoryInterface $productDataFactory,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly AdministratorGridFacade $administratorGridFacade,
        protected readonly ProductListAdminFacade $productListAdminFacade,
        protected readonly AdvancedSearchProductFacade $advancedSearchProductFacade,
        protected readonly ProductVariantFacade $productVariantFacade,
        protected readonly ProductExtension $productExtension,
        protected readonly Domain $domain,
        protected readonly UnitFacade $unitFacade,
        protected readonly Setting $setting,
        protected readonly AvailabilityFacade $availabilityFacade,
    ) {
    }

    /**
     * @Route("/product/edit/{id}", requirements={"id" = "\d+"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     */
    public function editAction(Request $request, int $id)
    {
        $product = $this->productFacade->getById($id);
        $productData = $this->productDataFactory->createFromProduct($product);

        $form = $this->createForm(ProductFormType::class, $productData, ['product' => $product]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->setSellingToUntilEndOfDay($productData);
            $this->productFacade->edit($id, $productData);

            $this
                ->addSuccessFlashTwig(
                    t('Product <strong><a href="{{ url }}">{{ product|productDisplayName }}</a></strong> modified'),
                    [
                        'product' => $product,
                        'url' => $this->generateUrl('admin_product_edit', ['id' => $product->getId()]),
                    ],
                );

            return $this->redirectToRoute('admin_product_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing product - %name%', ['%name%' => $this->productExtension->getProductDisplayName($product)]),
        );

        $viewParameters = [
            'form' => $form->createView(),
            'product' => $product,
            'domains' => $this->domain->getAll(),
        ];

        return $this->render('@ShopsysFramework/Admin/Content/Product/edit.html.twig', $viewParameters);
    }

    /**
     * @Route("/product/new/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function newAction(Request $request)
    {
        try {
            $productData = $this->productDataFactory->create();
        } catch (NotFoundHttpException $e) {
            $this->addErrorFlash(t('Please fill all default values before creating a product'));

            return $this->redirectToRoute('admin_product_list');
        }

        $form = $this->createForm(ProductFormType::class, $productData, ['product' => null]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->setSellingToUntilEndOfDay($productData);
            $product = $this->productFacade->create($productData);

            $this
                ->addSuccessFlashTwig(
                    t('Product <strong><a href="{{ url }}">{{ product|productDisplayName }}</a></strong> created'),
                    [
                        'product' => $product,
                        'url' => $this->generateUrl('admin_product_edit', ['id' => $product->getId()]),
                    ],
                );

            return $this->redirectToRoute('admin_product_list', ['id' => $product->getId()]);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/Product/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/product/list/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function listAction(Request $request)
    {
        /** @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator */
        $administrator = $this->getUser();
        $advancedSearchForm = $this->advancedSearchProductFacade->createAdvancedSearchForm($request);
        $advancedSearchData = $advancedSearchForm->getData();
        $quickSearchData = new QuickSearchFormData();

        $quickSearchForm = $this->createForm(QuickSearchFormType::class, $quickSearchData);

        // Cannot call $form->handleRequest() because the GET forms are not handled in POST request.
        // See: https://github.com/symfony/symfony/issues/12244
        $quickSearchForm->submit($request->query->get($quickSearchForm->getName()));

        $massActionForm = $this->createForm(ProductMassActionFormType::class);
        $massActionForm->handleRequest($request);

        $isAdvancedSearchFormSubmitted = $this->advancedSearchProductFacade->isAdvancedSearchFormSubmitted($request);

        if ($isAdvancedSearchFormSubmitted) {
            $queryBuilder = $this->advancedSearchProductFacade->getQueryBuilderByAdvancedSearchData(
                $advancedSearchData,
            );
        } else {
            $queryBuilder = $this->productListAdminFacade->getQueryBuilderByQuickSearchData($quickSearchData);
        }

        $grid = $this->getGrid($queryBuilder);

        /** @var \Symfony\Component\Form\SubmitButton $submitButton */
        $submitButton = $massActionForm->get('submit');

        if ($submitButton->isClicked()) {
            $this->productMassActionFacade->doMassAction(
                $massActionForm->getData(),
                $queryBuilder,
                array_map('intval', $grid->getSelectedRowIds()),
            );

            $this->addSuccessFlash(t('Bulk editing done'));

            return $this->redirect($request->headers->get('referer', $this->generateUrl('admin_product_list')));
        }

        $this->administratorGridFacade->restoreAndRememberGridLimit($administrator, $grid);

        $productCanBeCreated = $this->productCanBeCreated();

        return $this->render('@ShopsysFramework/Admin/Content/Product/list.html.twig', [
            'gridView' => $grid->createView(),
            'quickSearchForm' => $quickSearchForm->createView(),
            'advancedSearchForm' => $advancedSearchForm->createView(),
            'massActionForm' => $massActionForm->createView(),
            'isAdvancedSearchFormSubmitted' => $this->advancedSearchProductFacade->isAdvancedSearchFormSubmitted(
                $request,
            ),
            'productCanBeCreated' => $productCanBeCreated,
        ]);
    }

    /**
     * @Route("/product/delete/{id}", requirements={"id" = "\d+"})
     * @CsrfProtection
     * @param int $id
     */
    public function deleteAction($id)
    {
        try {
            $product = $this->productFacade->getById($id);

            $this->productFacade->delete($id);

            $this->addSuccessFlashTwig(
                t('Product <strong>{{ product|productDisplayName }}</strong> deleted'),
                [
                    'product' => $product,
                ],
            );
        } catch (ProductNotFoundException $ex) {
            $this->addErrorFlash(t('Selected product doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_product_list');
    }

    /**
     * @Route("/product/get-advanced-search-rule-form/", methods={"post"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function getRuleFormAction(Request $request)
    {
        $ruleForm = $this->advancedSearchProductFacade->createRuleForm(
            $request->get('filterName'),
            $request->get('newIndex'),
        );

        return $this->render('@ShopsysFramework/Admin/Content/Product/AdvancedSearch/ruleForm.html.twig', [
            'rulesForm' => $ruleForm->createView(),
        ]);
    }

    /**
     * @Route("/product/create-variant/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function createVariantAction(Request $request)
    {
        $form = $this->createForm(VariantFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $mainVariant = $formData[VariantFormType::MAIN_VARIANT];

            try {
                $newMainVariant = $this->productVariantFacade->createVariant(
                    $mainVariant,
                    $formData[VariantFormType::VARIANTS],
                );

                $this->addSuccessFlashTwig(
                    t('Variant <strong><a href="{{ url }}">{{ productVariant|productDisplayName }}</a></strong> successfully created.'),
                    [
                        'productVariant' => $newMainVariant,
                        'url' => $this->generateUrl('admin_product_edit', ['id' => $newMainVariant->getId()]),
                    ],
                );

                return $this->redirectToRoute('admin_product_list');
            } catch (VariantException $ex) {
                $this->addErrorFlash(
                    t('Not possible to create variations of products that are already variant or main variant.'),
                );
            }
        }

        return $this->render('@ShopsysFramework/Admin/Content/Product/createVariant.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    protected function getGrid(QueryBuilder $queryBuilder)
    {
        $dataSource = new QueryBuilderWithRowManipulatorDataSource(
            $queryBuilder,
            'p.id',
            function ($row) {
                $product = $this->productFacade->getById($row['p']['id']);
                $row['product'] = $product;
                // actual visibility is rendered in the template, this is just a placeholder for column
                $row['visibility'] = null;

                return $row;
            },
        );

        $grid = $this->gridFactory->create('productList', $dataSource);
        $grid->enablePaging();
        $grid->enableSelecting();
        $grid->setDefaultOrder('name');

        $grid->addColumn('name', 'pt.name', t('Name'), true);
        $grid->addColumn('price', 'priceForProductList', t('Price'), true)->setClassAttribute('text-right');
        $grid->addColumn('visibility', 'visibility', t('Visibility'))
            ->setClassAttribute('text-center table-col table-col-10');

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_product_edit', ['id' => 'p.id']);
        $grid->addDeleteActionColumn('admin_product_delete', ['id' => 'p.id'])
            ->setConfirmMessage(t('Do you really want to remove this product?'));

        $grid->setTheme('@ShopsysFramework/Admin/Content/Product/listGrid.html.twig', [
            'VARIANT_TYPE_MAIN' => Product::VARIANT_TYPE_MAIN,
            'VARIANT_TYPE_VARIANT' => Product::VARIANT_TYPE_VARIANT,
        ]);

        return $grid;
    }

    /**
     * @Route("/product/visibility/{productId}")
     * @param int $productId
     */
    public function visibilityAction($productId)
    {
        $product = $this->productFacade->getById($productId);

        return $this->render('@ShopsysFramework/Admin/Content/Product/visibility.html.twig', [
            'product' => $product,
            'domains' => $this->domain->getAll(),
        ]);
    }

    /**
     * @return bool
     */
    protected function productCanBeCreated()
    {
        return count($this->unitFacade->getAll()) !== 0
            && $this->setting->get(Setting::DEFAULT_UNIT) !== 0
            && count($this->availabilityFacade->getAll()) !== 0;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData|null $productData
     */
    protected function setSellingToUntilEndOfDay(?ProductData $productData): void
    {
        if ($productData->sellingTo !== null) {
            $productData->sellingTo->modify('+1 day -1 second');
        }
    }
}
