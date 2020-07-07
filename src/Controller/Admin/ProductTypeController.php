<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Admin\Product\Type\ProductTypeFormType;
use App\Model\Product\Type\ProductTypeDataFactory;
use App\Model\Product\Type\ProductTypeFacade;
use App\Model\Product\Type\ProductTypeGridFactory;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductTypeController extends AdminBaseController
{
    /**
     * @var \App\Model\Product\Type\ProductTypeFacade
     */
    protected $productTypeFacade;

    /**
     * @var \App\Model\Product\Type\ProductTypeGridFactory
     */
    private $productTypeGridFactory;

    /**
     * @var \App\Model\Product\Type\ProductTypeDataFactory
     */
    private $productTypeDataFactory;

    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider
     */
    private $breadcrumbOverrider;

    /**
     * @param \App\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \App\Model\Product\Type\ProductTypeFacade $productTypeFacade
     * @param \App\Model\Product\Type\ProductTypeDataFactory $productTypeDataFactory
     * @param \App\Model\Product\Type\ProductTypeGridFactory $productTypeGridFactory
     */
    public function __construct(
        Domain $domain,
        BreadcrumbOverrider $breadcrumbOverrider,
        ProductTypeFacade $productTypeFacade,
        ProductTypeDataFactory $productTypeDataFactory,
        ProductTypeGridFactory $productTypeGridFactory
    ) {
        $this->productTypeFacade = $productTypeFacade;
        $this->productTypeGridFactory = $productTypeGridFactory;
        $this->productTypeDataFactory = $productTypeDataFactory;
        $this->domain = $domain;
        $this->breadcrumbOverrider = $breadcrumbOverrider;
    }

    /**
     * @Route("/product/type/list/")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): Response
    {
        $grid = $this->productTypeGridFactory->create();

        return $this->render('Admin/Content/ProductType/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @Route("/product/type/new")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request): Response
    {
        $productTypeData = $this->productTypeDataFactory->create();

        $form = $this->createForm(ProductTypeFormType::class, $productTypeData, ['edited_product_type' => null]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productType = $this->productTypeFacade->create($form->getData());

            $this
                ->addSuccessFlashTwig(
                    t('Typ produktu <strong><a href="{{ url }}">{{ productType.name }}</a></strong> je úspěšně vytvořen'),
                    [
                        'productType' => $productType,
                        'url' => $this->generateUrl('admin_producttype_edit', ['id' => $productType->getId()]),
                    ]
                );

            return $this->redirectToRoute('admin_producttype_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render(
            'Admin/Content/ProductType/new.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/product/type/edit/{id}", requirements={"id" = "\d+"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, int $id): Response
    {
        $productType = $this->productTypeFacade->getById($id);

        $productTypeData = $this->productTypeDataFactory->createFromProductType($productType);

        $form = $this->createForm(ProductTypeFormType::class, $productTypeData, ['edited_product_type' => $productType]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productType = $this->productTypeFacade->edit($id, $form->getData());

            $this
                ->addSuccessFlashTwig(
                    t('Typ produktu <strong><a href="{{ url }}">{{ productType.name }}</a></strong> je úspěšně upraven'),
                    [
                        'productType' => $productType,
                        'url' => $this->generateUrl('admin_producttype_edit', ['id' => $productType->getId()]),
                    ]
                );
            return $this->redirectToRoute('admin_producttype_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(t('Úprava typu produktu - %name%', ['%name%' => $productType->getName($this->domain->getLocale())]));

        return $this->render(
            'Admin/Content/ProductType/edit.html.twig',
            [
                'productType' => $productType,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/product/type/delete/{id}", requirements={"id" = "\d+"})
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(int $id): Response
    {
        try {
            $productType = $this->productTypeFacade->getById($id);

            $this->productTypeFacade->delete($productType);

            $this->addSuccessFlashTwig(
                t('Typ produktu <strong>{{ name }}</strong> byl smazán'),
                [
                    'name' => $productType->getName(),
                ]
            );
        } catch (\App\Model\Product\Type\Exception\ProductTypeIsBeingUsedException $ex) {
            $this->addErrorFlash(t('Zvolený typ je využíván a proto jej nelze nyní odstranit.'));
        } catch (\App\Model\Product\Type\Exception\ProductTypeNotFoundException $ex) {
            $this->addErrorFlash(t('Zvolený typ již neexistuje.'));
        }

        return $this->redirectToRoute('admin_producttype_list');
    }
}
