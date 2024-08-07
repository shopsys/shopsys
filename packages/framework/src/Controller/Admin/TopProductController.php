<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Form\Admin\Product\TopProduct\TopProductsFormType;
use Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade;
use Shopsys\FrontendApiBundle\Model\Product\ProductFrontendLimitProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TopProductController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade $topProductFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Shopsys\FrontendApiBundle\Model\Product\ProductFrontendLimitProvider $productFrontendLimitProvider
     */
    public function __construct(
        protected readonly TopProductFacade $topProductFacade,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly ProductFrontendLimitProvider $productFrontendLimitProvider,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    #[Route(path: '/product/top-product/list/')]
    public function listAction(Request $request)
    {
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $formData = [
            'products' => $this->getProductsForDomain($domainId),
        ];

        $form = $this->createForm(TopProductsFormType::class, $formData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $products = $form->getData()['products'];

            $this->topProductFacade->saveTopProductsForDomain($domainId, $products);

            $this->addSuccessFlash(t('Product settings on the main page successfully changed'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/TopProduct/list.html.twig', [
            'form' => $form->createView(),
            'productsFrontendLimit' => $this->productFrontendLimitProvider->getProductsFrontendLimit(),
        ]);
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    protected function getProductsForDomain($domainId)
    {
        $topProducts = $this->topProductFacade->getAll($domainId);
        $products = [];

        foreach ($topProducts as $topProduct) {
            $products[] = $topProduct->getProduct();
        }

        return $products;
    }
}
