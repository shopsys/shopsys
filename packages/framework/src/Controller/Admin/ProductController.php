<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanCreate;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Attribute\RequireRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Form\Admin\Product\ProductFormType;
use Shopsys\FrameworkBundle\Form\Admin\Product\ProductMassActionFormType;
use Shopsys\FrameworkBundle\Form\Admin\Product\VariantFormType;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchProductFacade;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Exception\VariantException;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListAdminFacade;
use Shopsys\FrameworkBundle\Model\Product\MassAction\ProductMassActionFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductData;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactory;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductGridFactory;
use Shopsys\FrameworkBundle\Model\Product\ProductVariantFacade;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade;
use Shopsys\FrameworkBundle\Twig\ProductExtension;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_PRODUCT)]
class ProductController extends AdminBaseController
{
    public function __construct(
        protected readonly ProductMassActionFacade $productMassActionFacade,
        protected readonly ProductFacade $productFacade,
        protected readonly ProductDataFactory $productDataFactory,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly AdministratorGridFacade $administratorGridFacade,
        protected readonly ProductListAdminFacade $productListAdminFacade,
        protected readonly AdvancedSearchProductFacade $advancedSearchProductFacade,
        protected readonly ProductVariantFacade $productVariantFacade,
        protected readonly ProductExtension $productExtension,
        protected readonly Domain $domain,
        protected readonly UnitFacade $unitFacade,
        protected readonly Setting $setting,
        protected readonly ProductGridFactory $productGridFactory,
    ) {
    }

    #[Route(path: '/product/edit/{id}', requirements: ['id' => '\d+'])]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function editAction(Request $request, int $id): Response
    {
        $product = $this->productFacade->getById($id);
        $productData = $this->productDataFactory->createFromProduct($product);

        $form = $this->createForm(ProductFormType::class, $productData, ['product' => $product]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->setSellingToUntilEndOfDay($productData);
            $this->productFacade->edit($id, $productData, ProductRecalculationPriorityEnum::HIGH);

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

        return $this->render('@ShopsysAdministration/content/product/edit.html.twig', $viewParameters);
    }

    #[Route(path: '/product/new/')]
    #[CanCreate]
    public function newAction(Request $request): Response
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
            $product = $this->productFacade->create($productData, ProductRecalculationPriorityEnum::HIGH);

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

        return $this->render('@ShopsysAdministration/content/product/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/product/list/')]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function listAction(Request $request): Response
    {
        $advancedSearchForm = $this->advancedSearchProductFacade->createAdvancedSearchForm($request);
        $advancedSearchData = $advancedSearchForm->getData();
        $quickSearchData = new QuickSearchFormData();

        $quickSearchForm = $this->createForm(QuickSearchFormType::class, $quickSearchData);

        // Cannot call $form->handleRequest() because the GET forms are not handled in POST request.
        // See: https://github.com/symfony/symfony/issues/12244
        $quickSearchForm->submit($request->query->all($quickSearchForm->getName()));

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

        $this->administratorGridFacade->restoreAndRememberGridLimit($this->getCurrentAdministrator(), $grid);

        $productCanBeCreated = $this->productCanBeCreated();

        return $this->render('@ShopsysAdministration/content/product/list.html.twig', [
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

    #[Route(path: '/product/delete/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAction(int $id): Response
    {
        try {
            $product = $this->productFacade->getById($id);

            $this->productFacade->delete($id, ProductRecalculationPriorityEnum::HIGH);

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

    #[Route(path: '/product/get-advanced-search-rule-form/', methods: ['post'])]
    #[CanView]
    public function getRuleFormAction(Request $request): Response
    {
        $ruleForm = $this->advancedSearchProductFacade->createRuleForm(
            $request->request->getString('filterName'),
            $request->request->getString('newIndex'),
        );

        return $this->render('@ShopsysAdministration/content/product/advancedSearch/ruleForm.html.twig', [
            'rulesForm' => $ruleForm->createView(),
        ]);
    }

    #[Route(path: '/product/create-variant/')]
    #[CanCreate]
    public function createVariantAction(Request $request): Response
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
            } catch (VariantException) {
                $this->addErrorFlash(
                    t('Not possible to create variations of products that are already variant or main variant.'),
                );
            }
        }

        return $this->render('@ShopsysAdministration/content/product/createVariant.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    protected function getGrid(QueryBuilder $queryBuilder): Grid
    {
        return $this->productGridFactory->getProductControllerGrid($queryBuilder);
    }

    #[Route(path: '/product/visibility/{productId}')]
    #[RequireRole(SystemRole::ADMIN)]
    public function visibilityAction(int $productId, bool $withHeadline = true): Response
    {
        $product = $this->productFacade->getById($productId);

        return $this->render('@ShopsysAdministration/content/product/visibilityItems.html.twig', [
            'product' => $product,
            'domains' => $this->domain->getAdminEnabledDomains(),
            'withHeadline' => $withHeadline,
        ]);
    }

    #[Route(path: '/product/edit/catnum-exists')]
    #[RequireRole(SystemRole::ADMIN)]
    public function catnumExistsAction(Request $request): Response
    {
        $catnum = $request->query->has('catnum') ? $request->query->getString('catnum') : null;
        $currentProductCatnum = $request->query->getString('currentProductCatnum');

        if ($catnum === null || $catnum === $currentProductCatnum) {
            return new JsonResponse(false);
        }

        $productByCatnum = $this->productFacade->findByCatnum($catnum);

        return new JsonResponse($productByCatnum !== null);
    }

    /**
     * This route is used by GrapesJS to load Names of products
     */
    #[Route(path: '/product/names-by-catnums', methods: ['post'], condition: 'request.isXmlHttpRequest()')]
    #[RequireRole(SystemRole::ADMIN)]
    public function productNamesByCatnumsAction(Request $request): JsonResponse
    {
        $catnums = $request->request->all('catnums');

        $response = [];
        $products = $this->productFacade->findAllByCatnums($catnums);

        foreach ($products as $product) {
            if ($product->getCatnum() !== null) {
                $response[$product->getCatnum()] = $product->getName();
            }
        }

        return new JsonResponse($response);
    }

    protected function productCanBeCreated(): bool
    {
        return $this->unitFacade->isAtLeastOneUnitCreated()
            && $this->setting->get(Setting::DEFAULT_UNIT) !== 0;
    }

    protected function setSellingToUntilEndOfDay(?ProductData $productData): void
    {
        if ($productData?->sellingTo !== null) {
            $productData->sellingTo = $productData->sellingTo->modify('+1 day -1 second');
        }
    }
}
