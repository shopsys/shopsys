<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Form\Front\Cart\AddProductFormType;
use App\Form\Front\Cart\CartFormType;
use App\Model\Order\Preview\OrderPreviewFactory;
use App\Model\Order\Preview\OrderPreviewSplittingFacade;
use App\Model\Product\Availability\ProductAvailabilityFacade;
use App\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FlashMessage\ErrorExtractor;
use Shopsys\FrameworkBundle\Model\Cart\AddProductResult;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Module\ModuleList;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\ReadModelBundle\Product\Action\ProductActionView;
use Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFacadeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class CartController extends FrontBaseController
{
    public const AFTER_ADD_WINDOW_ACCESSORIES_LIMIT = 3;

    public const RECALCULATE_ONLY_PARAMETER_NAME = 'recalculateOnly';

    public const PAGES_WITH_DISABLED_CART_HOVER = ['front_cart', 'front_error_page', 'front_order_index', 'front_order_sent'];

    /**
     * @var \App\Model\Cart\CartFacade
     */
    private $cartFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \App\Model\Order\Preview\OrderPreviewFactory
     */
    private $orderPreviewFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FlashMessage\ErrorExtractor
     */
    private $errorExtractor;

    /**
     * @var \Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFacadeInterface
     */
    private $listedProductViewFacade;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @var \App\Model\Order\Preview\OrderPreviewSplittingFacade
     */
    private $orderPreviewSplittingFacade;

    /**
     * @var \App\Model\Product\Availability\ProductAvailabilityFacade
     */
    private $productAvailabilityFacade;

    /**
     * @var \App\Model\Product\ProductFacade
     */
    private $productFacade;

    /**
     * @param \App\Model\Cart\CartFacade $cartFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
     * @param \Shopsys\FrameworkBundle\Component\FlashMessage\ErrorExtractor $errorExtractor
     * @param \Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFacadeInterface $listedProductViewFacade
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \App\Model\Order\Preview\OrderPreviewSplittingFacade $cartSplittingFacade
     * @param \App\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     * @param \App\Model\Product\ProductFacade $productFacade
     */
    public function __construct(
        CartFacade $cartFacade,
        Domain $domain,
        OrderPreviewFactory $orderPreviewFactory,
        ErrorExtractor $errorExtractor,
        ListedProductViewFacadeInterface $listedProductViewFacade,
        RequestStack $requestStack,
        OrderPreviewSplittingFacade $cartSplittingFacade,
        ProductAvailabilityFacade $productAvailabilityFacade,
        ProductFacade $productFacade
    ) {
        $this->cartFacade = $cartFacade;
        $this->domain = $domain;
        $this->orderPreviewFactory = $orderPreviewFactory;
        $this->errorExtractor = $errorExtractor;
        $this->listedProductViewFacade = $listedProductViewFacade;
        $this->requestStack = $requestStack;
        $this->orderPreviewSplittingFacade = $cartSplittingFacade;
        $this->productAvailabilityFacade = $productAvailabilityFacade;
        $this->productFacade = $productFacade;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function indexAction(Request $request)
    {
        $domainId = $this->domain->getId();

        /** @var \App\Model\Cart\Cart|null $cart */
        $cart = $this->cartFacade->findCartOfCurrentCustomerUser();
        $cartItems = $cart === null ? [] : $cart->getItems();
        $oversizedCount = 0;

        $cartFormData = ['quantities' => []];
        $maximumOrderQuantity = [];

        foreach ($cartItems as $cartItem) {
            $cartFormData['quantities'][$cartItem->getId()] = $cartItem->getQuantity();
            if ($cartItem->getProduct()->isOversized($domainId)) {
                $oversizedCount++;
            }
            $maximumOrderQuantity[$cartItem->getProduct()->getId()] =
                $this->productAvailabilityFacade->getMaximumOrderQuantity($cartItem->getProduct(), $domainId);
        }

        $form = $this->createForm(CartFormType::class, $cartFormData);
        $form->handleRequest($request);

        $invalidCart = false;
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->cartFacade->changeQuantities($form->getData()['quantities']);

                if (!$request->get(self::RECALCULATE_ONLY_PARAMETER_NAME, false)) {
                    return $this->redirectToRoute('front_order_index');
                }
            } catch (\Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidQuantityException $ex) {
                $invalidCart = true;
            }
        } elseif ($form->isSubmitted()) {
            $invalidCart = true;
        }

        if ($invalidCart) {
            $this->addErrorFlash(
                t('Please make sure that you entered right quantity of all items in cart.')
            );
        }

        $splitOrderPreview = $this->orderPreviewSplittingFacade->createSplitOrderPreviewForCurrentCustomer(null);

        return $this->render('Front/Content/Cart/index.html.twig', [
            'splitOrderPreview' => $splitOrderPreview,
            'cart' => $cart,
            'form' => $form->createView(),
            'showOversizedMsg' => $oversizedCount !== 0 && $oversizedCount !== count($cartItems),
            'maximumOrderQuantity' => $maximumOrderQuantity,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function boxAction(Request $request)
    {
        $orderPreview = $this->orderPreviewFactory->createForCurrentUser();

        return $this->render('Front/Inline/Cart/cartBox.html.twig', [
            'cart' => $this->cartFacade->findCartOfCurrentCustomerUser(),
            'productsPrice' => $orderPreview->getProductsPrice(),
            'isIntentActive' => $request->query->getBoolean('isIntentActive'),
            'isCartHoverEnable' => $this->isCartHoverEnable(),
            'loadItems' => $request->query->getBoolean('loadItems'),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function boxDetailAction(Request $request): Response
    {
        $orderPreview = $this->orderPreviewFactory->createForCurrentUser();

        return $this->render('Front/Inline/Cart/cartBox.html.twig', [
            'cart' => $this->cartFacade->findCartOfCurrentCustomerUser(),
            'productsPrice' => $orderPreview->getProductsPrice(),
            'isIntentActive' => true,
            'isCartHoverEnable' => false,
            'loadItems' => true,
        ]);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param string $type
     * @deprecated This action is deprecated since 7.3.0, use App\Controller\Front\CartController:productAction instead
     */
    public function addProductFormAction(Product $product, $type = 'normal')
    {
        $form = $this->createForm(AddProductFormType::class, ['productId' => $product->getId()], [
            'action' => $this->generateUrl('front_cart_add_product'),
        ]);

        $productAvailable = $this->productAvailabilityFacade->isProductAvailableOnDomainOrHasPreorder(
            $product,
            $this->domain->getId()
        );

        $availableAddQuantity = $this->productAvailabilityFacade->getMaximumOrderQuantity($product, $this->domain->getId());
        if (!$product->hasPreorder()) {
            $cart = $this->cartFacade->findCartOfCurrentCustomerUser();
            if ($cart) {
                /** @var \App\Model\Cart\Item\CartItem $cartItem */
                $cartItem = $cart->findCartItemByProduct($product);
                if ($cartItem !== null) {
                    $availableAddQuantity -= $cartItem->getQuantity();
                }
            }
        }

        return $this->render('Front/Inline/Cart/addProduct.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
            'type' => $type,
            'productAvailable' => $productAvailable,
            'availableAddQuantity' => $availableAddQuantity,
        ]);
    }

    /**
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionView $productActionView
     * @param string $type
     */
    public function productActionAction(ProductActionView $productActionView, $type = 'normal')
    {
        $form = $this->createForm(AddProductFormType::class, ['productId' => $productActionView->getId()], [
            'action' => $this->generateUrl('front_cart_add_product'),
        ]);

        $product = $this->productFacade->getById($productActionView->getId());

        $productAvailable = $this->productAvailabilityFacade->isProductAvailableOnDomainOrHasPreorder(
            $product,
            $this->domain->getId()
        );

        return $this->render('Front/Inline/Cart/productAction.html.twig', [
            'form' => $form->createView(),
            'productActionView' => $productActionView,
            'type' => $type,
            'productAvailable' => $productAvailable,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function addProductAction(Request $request)
    {
        $form = $this->createForm(AddProductFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $formData = $form->getData();

                $addProductResult = $this->cartFacade->addProductToCart($formData['productId'], (int)$formData['quantity']);

                $this->sendAddProductResultFlashMessage($addProductResult);
            } catch (\Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException $ex) {
                $this->addErrorFlash(t('Selected product no longer available or doesn\'t exist.'));
            } catch (\Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidQuantityException $ex) {
                $this->addErrorFlash(t('Please enter valid quantity you want to add to cart.'));
            } catch (\Shopsys\FrameworkBundle\Model\Cart\Exception\CartException $ex) {
                $this->addErrorFlash(t('Unable to add product to cart'));
            }
        } else {
            // Form errors list in flash message is temporary solution.
            // We need to determine couse of error when adding product to cart.
            $formErrors = $this->errorExtractor->getAllErrorsAsArray($form, $this->getErrorMessages());
            $this->addErrorFlashTwig(
                t('Unable to add product to cart:<br/><ul><li>{{ errors|raw }}</li></ul>'),
                [
                    'errors' => implode('</li><li>', $formErrors),
                ]
            );
        }

        if ($request->headers->get('referer')) {
            $redirectTo = $request->headers->get('referer');
        } else {
            $redirectTo = $this->generateUrl('front_homepage');
        }

        return $this->redirect($redirectTo);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function addProductAjaxAction(Request $request)
    {
        $form = $this->createForm(AddProductFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $formData = $form->getData();

                $addProductResult = $this->cartFacade->addProductToCart($formData['productId'], (int)$formData['quantity']);

                $this->sendAddProductResultFlashMessage($addProductResult);

                $accessories = $this->listedProductViewFacade->getAccessories(
                    $addProductResult->getCartItem()->getProduct()->getId(),
                    self::AFTER_ADD_WINDOW_ACCESSORIES_LIMIT
                );

                return $this->render('Front/Inline/Cart/afterAddWindow.html.twig', [
                    'accessories' => $accessories,
                    'ACCESSORIES_ON_BUY' => ModuleList::ACCESSORIES_ON_BUY,
                ]);
            } catch (\Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException $ex) {
                $this->addErrorFlash(t('Selected product no longer available or doesn\'t exist.'));
            } catch (\Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidQuantityException $ex) {
                $this->addErrorFlash(t('Please enter valid quantity you want to add to cart.'));
            } catch (\Shopsys\FrameworkBundle\Model\Cart\Exception\CartException $ex) {
                $this->addErrorFlash(t('Unable to add product to cart'));
            }
        } else {
            // Form errors list in flash message is temporary solution.
            // We need to determine couse of error when adding product to cart.
            $formErrors = $this->errorExtractor->getAllErrorsAsArray($form, $this->getErrorMessages());
            $this->addErrorFlashTwig(
                t('Unable to add product to cart:<br/><ul><li>{{ errors|raw }}</li></ul>'),
                [
                    'errors' => implode('</li><li>', $formErrors),
                ]
            );
        }

        return $this->forward(FlashMessageController::class . ':indexAction');
    }

    /**
     * @param \App\Model\Cart\AddProductResult $addProductResult
     */
    private function sendAddProductResultFlashMessage(
        AddProductResult $addProductResult
    ) {
        if ($addProductResult->getIsNew()) {
            $this->addSuccessFlashTwig(
                t('Product <strong>{{ name }}</strong> ({{ quantity|formatNumber }} {{ unitName }}) added to the cart'),
                [
                    'name' => $addProductResult->getCartItem()->getName(),
                    'quantity' => $addProductResult->getAddedQuantity(),
                    'unitName' => $addProductResult->getCartItem()->getProduct()->getUnit()->getName(),
                ]
            );
        } elseif ($addProductResult->getNotOnStockQuantity() == $addProductResult->getAddedQuantity()) {
            $this->addErrorFlashTwig(
                t('V košíku máte maximální dostupné množství, nelze přidat další (celkem již {{ quantity|formatNumber }} {{ unitName }})'),
                [
                    'quantity' => $addProductResult->getCartItem()->getQuantity(),
                    'unitName' => $addProductResult->getCartItem()->getProduct()->getUnit()->getName(),
                ]
            );
        } else {
            $this->addSuccessFlashTwig(
                t('Product <strong>{{ name }}</strong> added to the cart (total amount {{ quantity|formatNumber }} {{ unitName }})'),
                [
                    'name' => $addProductResult->getCartItem()->getName(),
                    'quantity' => $addProductResult->getCartItem()->getQuantity(),
                    'unitName' => $addProductResult->getCartItem()->getProduct()->getUnit()->getName(),
                ]
            );
        }
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $cartItemId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request, int $cartItemId): Response
    {
        $token = $request->query->get('_token');

        if ($this->isCsrfTokenValid('front_cart_delete_' . $cartItemId, $token)) {
            try {
                $productName = $this->cartFacade->getProductByCartItemId($cartItemId)->getName();

                $this->cartFacade->deleteCartItem($cartItemId);

                $this->addSuccessFlashTwig(
                    t('Product {{ name }} removed from cart'),
                    ['name' => $productName]
                );
            } catch (\Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidCartItemException $ex) {
                $this->addErrorFlash(t('Unable to remove item from cart. The item is probably already removed.'));
            }
        } else {
            $this->addErrorFlash(
                t('Unable to remove item from cart. The link for removing it probably expired, try it again.')
            );
        }

        return $this->redirectToRoute('front_cart');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $cartItemId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAjaxAction(Request $request, int $cartItemId): Response
    {
        $token = $request->query->get('_token');

        if ($this->isCsrfTokenValid('front_cart_delete_' . $cartItemId, $token)) {
            try {
                $this->cartFacade->deleteCartItem($cartItemId);
            } catch (\Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidCartItemException $ex) {
                return $this->json([
                    'success' => false,
                    'errorMessage' => t('Unable to remove item from cart. The item is probably already removed.'),
                ]);
            }
        } else {
            return $this->json([
                'success' => false,
                'errorMessage' => t('Unable to remove item from cart. The link for removing it probably expired, try it again.'),
            ]);
        }

        return $this->json([
            'success' => true,
        ]);
    }

    /**
     * @return bool
     */
    private function isCartHoverEnable(): bool
    {
        $masterRequest = $this->requestStack->getMasterRequest();
        if ($masterRequest === null) {
            return false;
        }

        return !in_array($masterRequest->get('_route'), self::PAGES_WITH_DISABLED_CART_HOVER, true);
    }
}
