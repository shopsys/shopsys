<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Shopsys\FrameworkBundle\Controller\Admin\ProductController as BaseProductController;
use Shopsys\FrameworkBundle\Form\Admin\Product\ProductFormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @property \App\Model\Product\ProductFacade $productFacade
 * @property \App\Model\Product\ProductDataFactory $productDataFactory
 * @property \App\Component\Setting\Setting $setting
 * @property \App\Model\Product\Availability\AvailabilityFacade $availabilityFacade
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @method setSellingToUntilEndOfDay(\App\Model\Product\ProductData|null $productData)
 * @method __construct(\Shopsys\FrameworkBundle\Model\Product\MassAction\ProductMassActionFacade $productMassActionFacade, \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory, \App\Model\Product\ProductFacade $productFacade, \App\Model\Product\ProductDataFactory $productDataFactory, \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider, \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade $administratorGridFacade, \Shopsys\FrameworkBundle\Model\Product\Listing\ProductListAdminFacade $productListAdminFacade, \Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchProductFacade $advancedSearchProductFacade, \App\Model\Product\ProductVariantFacade $productVariantFacade, \Shopsys\FrameworkBundle\Twig\ProductExtension $productExtension, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \App\Model\Product\Unit\UnitFacade $unitFacade, \App\Component\Setting\Setting $setting, \App\Model\Product\Availability\AvailabilityFacade $availabilityFacade)
 * @property \App\Model\Product\ProductVariantFacade $productVariantFacade
 * @property \App\Model\Product\Unit\UnitFacade $unitFacade
 */
class ProductController extends BaseProductController
{
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

        $viewParameters = [
            'form' => $form->createView(),
            'product' => $product,
            'domains' => $this->domain->getAll(),
            'productParameterValuesData' => $productData->parameters,
        ];

        return $this->render('@ShopsysFramework/Admin/Content/Product/edit.html.twig', $viewParameters);
    }

    /**
     * @Route("/product/edit/catnum-exists")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function catnumExistsAction(Request $request): Response
    {
        $catnum = $request->get('catnum');
        $currentProductCatnum = $request->get('currentProductCatnum');

        if ($catnum === null || $catnum === $currentProductCatnum) {
            return new JsonResponse(false);
        }

        $productByCatnum = $this->productFacade->findByCatnum($catnum);

        return new JsonResponse($productByCatnum !== null);
    }

    /**
     * This route is used by GrapesJS to load Names of products
     *
     * @Route("/product/names-by-catnums/{catnums}")
     * @param string $catnums
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function productNamesByCatnumsAction(string $catnums): JsonResponse
    {
        $response = [];
        $products = $this->productFacade->findAllByCatnums(explode(',', $catnums));

        foreach ($products as $product) {
            $response[$product->getCatnum()] = $product->getName();
        }

        return new JsonResponse($response);
    }
}
