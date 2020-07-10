<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Model\Product\Package\ProductPackageRepository;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Controller\Admin\ProductController as BaseProductController;
use Shopsys\FrameworkBundle\Form\Admin\Product\ProductFormType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchProductFacade;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListAdminFacade;
use Shopsys\FrameworkBundle\Model\Product\MassAction\ProductMassActionFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductVariantFacade;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade;
use Shopsys\FrameworkBundle\Twig\ProductExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @property \App\Model\Product\ProductFacade $productFacade
 * @property \App\Model\Product\ProductDataFactory $productDataFactory
 * @property \App\Component\Setting\Setting $setting
 * @property \App\Model\Product\Availability\AvailabilityFacade $availabilityFacade
 * @property \App\Component\Domain\Domain $domain
 */
class ProductController extends BaseProductController
{
    /**
     * @var \App\Model\Product\Package\ProductPackageRepository
     */
    private $productPackageRepository;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\MassAction\ProductMassActionFacade $productMassActionFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \App\Model\Product\ProductFacade $productFacade
     * @param \App\Model\Product\ProductDataFactory $productDataFactory
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade $administratorGridFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Listing\ProductListAdminFacade $productListAdminFacade
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchProductFacade $advancedSearchProductFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVariantFacade $productVariantFacade
     * @param \Shopsys\FrameworkBundle\Twig\ProductExtension $productExtension
     * @param \App\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade $unitFacade
     * @param \App\Component\Setting\Setting $setting
     * @param \App\Model\Product\Availability\AvailabilityFacade $availabilityFacade
     * @param \App\Model\Product\Package\ProductPackageRepository $productPackageRepository
     */
    public function __construct(
        ProductMassActionFacade $productMassActionFacade,
        GridFactory $gridFactory,
        ProductFacade $productFacade,
        ProductDataFactoryInterface $productDataFactory,
        BreadcrumbOverrider $breadcrumbOverrider,
        AdministratorGridFacade $administratorGridFacade,
        ProductListAdminFacade $productListAdminFacade,
        AdvancedSearchProductFacade $advancedSearchProductFacade,
        ProductVariantFacade $productVariantFacade,
        ProductExtension $productExtension,
        Domain $domain,
        UnitFacade $unitFacade,
        Setting $setting,
        AvailabilityFacade $availabilityFacade,
        ProductPackageRepository $productPackageRepository
    ) {
        parent::__construct(
            $productMassActionFacade,
            $gridFactory,
            $productFacade,
            $productDataFactory,
            $breadcrumbOverrider,
            $administratorGridFacade,
            $productListAdminFacade,
            $advancedSearchProductFacade,
            $productVariantFacade,
            $productExtension,
            $domain,
            $unitFacade,
            $setting,
            $availabilityFacade
        );
        $this->productPackageRepository = $productPackageRepository;
    }

    /**
     * @Route("/product/edit/{id}", requirements={"id" = "\d+"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param mixed $id
     */
    public function editAction(Request $request, $id)
    {
        $product = $this->productFacade->getById($id);
        $productData = $this->productDataFactory->createFromProduct($product);

        $form = $this->createForm(ProductFormType::class, $productData, ['product' => $product]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->productFacade->edit($id, $form->getData());

            $this
                ->addSuccessFlashTwig(
                    t('Product <strong><a href="{{ url }}">{{ product|productDisplayName }}</a></strong> modified'),
                    [
                        'product' => $product,
                        'url' => $this->generateUrl('admin_product_edit', ['id' => $product->getId()]),
                    ]
                );

            return $this->redirectToRoute('admin_product_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(t('Editing product - %name%', ['%name%' => $this->productExtension->getProductDisplayName($product)]));

        /** @var \App\Model\Product\Product $product */
        $productPackageGridView = $this->getProductPackageGridByProduct($product)->createView();

        $viewParameters = [
            'form' => $form->createView(),
            'product' => $product,
            'domains' => $this->domain->getAll(),
            'productPackageGridView' => $productPackageGridView,
            'productParameterValuesData' => $productData->parameters,
        ];

        return $this->render('/Admin/Content/Product/edit.html.twig', $viewParameters);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    private function getProductPackageGridByProduct(Product $product): Grid
    {
        $queryBuilder = $this->productPackageRepository->getQueryBuilderForProductPackagesByProduct($product);
        $queryBuilder->orderBy('pp.position');
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'pp.id');

        $grid = $this->gridFactory->create('productPackagesList', $dataSource);

        $grid->addColumn('position', 'pp.position', t('Pořadí'));
        $grid->addColumn('length', 'pp.length', t('Délka'));
        $grid->addColumn('width', 'pp.width', t('Šířka'));
        $grid->addColumn('height', 'pp.height', t('Výška'));
        $grid->addColumn('weight', 'pp.weight', t('Váha'));

        return $grid;
    }
}
