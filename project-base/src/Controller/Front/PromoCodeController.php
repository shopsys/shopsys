<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\InvalidPromoCodeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PromoCodeController extends FrontBaseController
{
    public const PROMO_CODE_PARAMETER = 'code';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade
     */
    private $currentPromoCodeFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     */
    public function __construct(
        CurrentPromoCodeFacade $currentPromoCodeFacade
    ) {
        $this->currentPromoCodeFacade = $currentPromoCodeFacade;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(): Response
    {
        return $this->render('Front/Content/Order/PromoCode/index.html.twig', [
            'validEnteredPromoCode' => $this->currentPromoCodeFacade->getValidEnteredPromoCodeOrNull(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function applyAction(Request $request): JsonResponse
    {
        $promoCode = $request->get(self::PROMO_CODE_PARAMETER);
        try {
            $this->currentPromoCodeFacade->setEnteredPromoCode($promoCode);
        } catch (InvalidPromoCodeException $ex) {
            return new JsonResponse([
                'result' => false,
                'message' => t('Promo code invalid. Check it, please.'),
            ]);
        }
        $this->addSuccessFlash(t('Promo code added to order'));

        return new JsonResponse(['result' => true]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction(): RedirectResponse
    {
        $this->currentPromoCodeFacade->removeEnteredPromoCode();
        $this->addSuccessFlash(t('Promo code removed from order'));

        return $this->redirectToRoute('front_cart');
    }
}
