<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Shopsys\FrameworkBundle\Controller\Admin\ProductController as BaseProductController;
use Shopsys\FrameworkBundle\Form\Admin\Product\ProductFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @property \App\Model\Product\ProductFacade $productFacade
 * @property \App\Model\Product\ProductDataFactory $productDataFactory
 * @property \App\Component\Setting\Setting $setting
 * @property \App\Model\Product\Availability\AvailabilityFacade $availabilityFacade
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @method setSellingToUntilEndOfDay(\App\Model\Product\ProductData|null $productData)
 * @method __construct(\Shopsys\FrameworkBundle\Model\Product\MassAction\ProductMassActionFacade $productMassActionFacade, \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory, \App\Model\Product\ProductFacade $productFacade, \App\Model\Product\ProductDataFactory $productDataFactory, \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider, \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade $administratorGridFacade, \Shopsys\FrameworkBundle\Model\Product\Listing\ProductListAdminFacade $productListAdminFacade, \Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchProductFacade $advancedSearchProductFacade, \Shopsys\FrameworkBundle\Model\Product\ProductVariantFacade $productVariantFacade, \Shopsys\FrameworkBundle\Twig\ProductExtension $productExtension, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade $unitFacade, \App\Component\Setting\Setting $setting, \App\Model\Product\Availability\AvailabilityFacade $availabilityFacade)
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

        return $this->render('/Admin/Content/Product/edit.html.twig', $viewParameters);
    }
}
