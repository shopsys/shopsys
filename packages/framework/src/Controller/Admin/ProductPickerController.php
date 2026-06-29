<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Security\Attribute\RequireRole;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\ProductAdvancedSearchFacade;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListAdminFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductGridFactory;
use Shopsys\FrameworkBundle\Twig\ImageExtension;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductPickerController extends AdminBaseController
{
    public function __construct(
        protected readonly AdministratorGridFacade $administratorGridFacade,
        protected readonly ProductListAdminFacade $productListAdminFacade,
        protected readonly ProductAdvancedSearchFacade $productAdvancedSearchFacade,
        protected readonly ProductFacade $productFacade,
        protected readonly ProductGridFactory $productGridFactory,
        protected readonly PricingSetting $pricingSetting,
        protected readonly ImageExtension $imageExtension,
    ) {
    }

    #[Route(path: '/product-picker/pick-multiple/{jsInstanceId}/{allowMainVariants}/{allowVariants}/{withPrice}/{domainId}')]
    #[RequireRole(SystemRole::ADMIN)]
    public function pickMultipleAction(
        Request $request,
        string $jsInstanceId,
        bool $allowMainVariants = true,
        bool $allowVariants = true,
        bool $withPrice = false,
        int $domainId = Domain::FIRST_DOMAIN_ID,
    ): Response {
        return $this->getPickerResponse(
            $request,
            [
                'isMultiple' => true,
            ],
            [
                'isMultiple' => true,
                'jsInstanceId' => $jsInstanceId,
                'allowMainVariants' => $allowMainVariants,
                'allowVariants' => $allowVariants,
                'withPrice' => $withPrice,
                'domainId' => $domainId,
            ],
        );
    }

    #[Route(path: '/product-picker/pick-single/{jsInstanceId}/', defaults: ['jsInstanceId' => '__js_instance_id__'])]
    #[RequireRole(SystemRole::ADMIN)]
    public function pickSingleAction(Request $request, string $jsInstanceId): Response
    {
        return $this->getPickerResponse(
            $request,
            [
                'isMultiple' => false,
            ],
            [
                'isMultiple' => false,
                'jsInstanceId' => $jsInstanceId,
                'allowMainVariants' => $request->query->getBoolean('allowMainVariants', true),
                'allowVariants' => $request->query->getBoolean('allowVariants', true),
            ],
        );
    }

    protected function getPickerResponse(Request $request, array $viewParameters, array $gridViewParameters): Response
    {
        $advancedSearchForm = $this->productAdvancedSearchFacade->createAdvancedSearchForm($request);
        $advancedSearchData = $advancedSearchForm->getData();
        $quickSearchData = new QuickSearchFormData();

        $quickSearchForm = $this->createForm(QuickSearchFormType::class, $quickSearchData);
        $quickSearchForm->handleRequest($request);

        $isAdvancedSearchFormSubmitted = $this->productAdvancedSearchFacade->isAdvancedSearchFormSubmitted($request);

        if ($isAdvancedSearchFormSubmitted) {
            $queryBuilder = $this->productAdvancedSearchFacade->getQueryBuilderByAdvancedSearchData(
                $advancedSearchData,
            );
        } else {
            $queryBuilder = $this->productListAdminFacade->getQueryBuilderByQuickSearchData($quickSearchData);
        }

        $grid = $this->productGridFactory->getProductPickerControllerGrid($queryBuilder, $gridViewParameters);
        $this->administratorGridFacade->restoreAndRememberGridLimit($this->getCurrentAdministrator(), $grid);

        $viewParameters['gridView'] = $grid->createView();
        $viewParameters['quickSearchForm'] = $quickSearchForm->createView();
        $viewParameters['advancedSearchForm'] = $advancedSearchForm->createView();
        $viewParameters['isAdvancedSearchFormSubmitted'] = $this->productAdvancedSearchFacade->isAdvancedSearchFormSubmitted(
            $request,
        );

        return $this->render('@ShopsysAdministration/content/productPicker/list.html.twig', $viewParameters);
    }

    #[Route(path: '/product-picker/basic-price/', methods: ['post'], condition: 'request.isXmlHttpRequest()')]
    #[RequireRole(SystemRole::ADMIN)]
    public function basicProductPriceAction(Request $request): Response
    {
        $productId = $request->request->getInt('productId');
        $domainId = $request->request->getInt('domainId');

        $basicPrice = $this->productFacade->getProductPriceForDefaultPricingGroup(
            $this->productFacade->getById($productId),
            $domainId,
        );

        $basicPriceAmount = $this->pricingSetting->getInputPriceType() === PricingSetting::PRICE_TYPE_WITH_VAT
            ? $basicPrice->getPrice()->getPriceWithVat()->getAmount()
            : $basicPrice->getPrice()->getPriceWithoutVat()->getAmount();

        return new JsonResponse([
            'basicPrice' => $basicPriceAmount,
        ]);
    }

    #[Route(path: '/product-picker/product-image/', methods: ['post'], condition: 'request.isXmlHttpRequest()')]
    #[RequireRole(SystemRole::ADMIN)]
    public function productImageAction(Request $request): Response
    {
        $productId = $request->request->getInt('productId');

        $product = $this->productFacade->getById($productId);

        return new JsonResponse([
            'imageHtml' => $this->imageExtension->getImageHtml($product),
        ]);
    }
}
