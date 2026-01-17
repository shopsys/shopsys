<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Product\TopProduct\TopProductsFormType;
use Shopsys\FrameworkBundle\Model\Product\ProductFrontendLimitProvider;
use Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_TOP_PRODUCT)]
class TopProductController extends AdminBaseController
{
    public function __construct(
        protected readonly TopProductFacade $topProductFacade,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly ProductFrontendLimitProvider $productFrontendLimitProvider,
    ) {
    }

    #[Route(path: '/product/top-product/list/')]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function listAction(Request $request): Response
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

        return $this->render('@ShopsysAdministration/content/topProduct/list.html.twig', [
            'form' => $form->createView(),
            'productsFrontendLimit' => $this->productFrontendLimitProvider->getProductsFrontendLimit(),
        ]);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    protected function getProductsForDomain(int $domainId): array
    {
        $topProducts = $this->topProductFacade->getAll($domainId);
        $products = [];

        foreach ($topProducts as $topProduct) {
            $products[] = $topProduct->getProduct();
        }

        return $products;
    }
}
