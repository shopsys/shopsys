<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Model\Order\PromoCode\CurrentPromoCodeFacade;
use App\Model\Order\PromoCode\Exception\NoLongerValidPromoCodeDateTimeException;
use App\Model\Order\PromoCode\Exception\NotYetValidPromoCodeDateTimeException;
use App\Model\Order\PromoCode\Exception\PromoCodeWithoutRelationWithAnyProductFromCurrentCartException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\InvalidPromoCodeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PromoCodeController extends FrontBaseController
{
    public const PROMO_CODE_PARAMETER = 'code';

    /**
     * @var \App\Model\Order\PromoCode\CurrentPromoCodeFacade|\App\Model\Order\PromoCode\CurrentPromoCodeFacade
     */
    private $currentPromoCodeFacade;

    /**
     * @param \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     */
    public function __construct(
        CurrentPromoCodeFacade $currentPromoCodeFacade
    ) {
        $this->currentPromoCodeFacade = $currentPromoCodeFacade;
    }

    public function indexAction()
    {
        return $this->render('Front/Content/Order/PromoCode/index.html.twig', [
            'validEnteredPromoCode' => $this->currentPromoCodeFacade->getValidEnteredPromoCodeOrNull(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function applyAction(Request $request)
    {
        $promoCode = $request->get(self::PROMO_CODE_PARAMETER);
        try {
            $this->currentPromoCodeFacade->setEnteredPromoCode($promoCode);
        } catch (InvalidPromoCodeException $ex) {
            return new JsonResponse([
                'result' => false,
                'message' => t('Promo code invalid. Check it, please.'),
            ]);
        } catch (NotYetValidPromoCodeDateTimeException $exception) {
            return new JsonResponse([
                'result' => false,
                'message' => t('Promo kód ještě není platný. Zkontrolujte ho, prosím.'),
            ]);
        } catch (NoLongerValidPromoCodeDateTimeException $exception) {
            return new JsonResponse([
                'result' => false,
                'message' => t('Promo kód už není platný. Zkontrolujte ho, prosím.'),
            ]);
        } catch (PromoCodeWithoutRelationWithAnyProductFromCurrentCartException $exception) {
            return new JsonResponse([
                'result' => false,
                'message' => t('Promo kód nelze uplatnit na žádný produkt v košíku. Zkontrolujte ho, prosím.'),
            ]);
        }

        $this->getFlashMessageSender()->addSuccessFlash(t('Promo code added to order'));

        return new JsonResponse(['result' => true]);
    }

    public function removeAction()
    {
        $this->currentPromoCodeFacade->removeEnteredPromoCode();
        $this->getFlashMessageSender()->addSuccessFlash(t('Promo code removed from order'));

        return $this->redirectToRoute('front_cart');
    }
}
