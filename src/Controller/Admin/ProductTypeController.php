<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Model\Product\Type\ProductTypeFacade;
use App\Model\Product\Type\ProductTypeInlineEdit;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Symfony\Component\HttpFoundation\Response;

class ProductTypeController extends AdminBaseController
{
    /**
     * @var \App\Model\Product\Type\ProductTypeFacade
     */
    protected $productTypeFacade;

    /**
     * @var \App\Model\Product\Type\ProductTypeInlineEdit
     */
    protected $productTypeInlineEdit;

    /**
     * ProductTypeController constructor.
     * @param \App\Model\Product\Type\ProductTypeFacade $productTypeFacade
     * @param \App\Model\Product\Type\ProductTypeInlineEdit $productTypeInlineEdit
     */
    public function __construct(
        ProductTypeFacade $productTypeFacade,
        ProductTypeInlineEdit $productTypeInlineEdit
    ) {
        $this->productTypeFacade = $productTypeFacade;
        $this->productTypeInlineEdit = $productTypeInlineEdit;
    }

    /**
     * @Route("/product/type/list/")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): Response
    {
        $grid = $this->productTypeInlineEdit->getGrid();

        return $this->render('Admin/Content/ProductType/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
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

            $this->getFlashMessageSender()->addSuccessFlashTwig(
                t('Typ produktu <strong>{{ name }}</strong> byl smazán'),
                [
                    'name' => $productType->getName(),
                ]
            );
        } catch (\App\Model\Product\Type\Exception\ProductTypeNotFoundException $ex) {
            $this->getFlashMessageSender()->addErrorFlash(t('Zvolený typ již neexistuje.'));
        }

        return $this->redirectToRoute('admin_producttype_list');
    }
}
