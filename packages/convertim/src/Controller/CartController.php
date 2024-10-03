<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Controller;

use Shopsys\ConvertimBundle\Model\Cart\CartFacade as ConvertimCartFacade;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFacade $cartFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\ConvertimBundle\Model\Cart\CartFacade $convertimCartFacade
     */
    public function __construct(
        protected readonly CartFacade $cartFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly ConvertimCartFacade $convertimCartFacade,
    ) {
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/cart-data', name: 'convertim_cart_data', methods: ['GET'])]
    public function cartDataAction(): Response
    {
        $cart = $this->cartFacade->findCartOfCurrentCustomerUser();
        $user = $this->currentCustomerUser->findCurrentCustomerUser();

        //        return new JsonResponse($this->convertimCartFacade->getCartData($cart, $user));
    }
}
