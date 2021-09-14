<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Model\Order\PromoCode\CurrentPromoCodeFacade;
use App\Model\Order\PromoCode\Exception\AvailableForRegisteredCustomerUserOnly;
use App\Model\Order\PromoCode\Exception\NoLongerValidPromoCodeDateTimeException;
use App\Model\Order\PromoCode\Exception\NotAvailableForCustomerUserPricingGroup;
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
    public function __construct(CurrentPromoCodeFacade $currentPromoCodeFacade)
    {
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
                'isValidWithoutProductsInCart' => false,
                'result' => false,
                'message' => t('Slevový kupón není platný nebo byl už použit. Prosím, zkontrolujte ho.'),
            ]);
        } catch (NotYetValidPromoCodeDateTimeException $exception) {
            return new JsonResponse([
                'isValidWithoutProductsInCart' => false,
                'result' => false,
                'message' => t('Promo kód ještě není platný. Zkontrolujte ho, prosím.'),
            ]);
        } catch (NoLongerValidPromoCodeDateTimeException $exception) {
            return new JsonResponse([
                'isValidWithoutProductsInCart' => false,
                'result' => false,
                'message' => t('Promo kód už není platný. Zkontrolujte ho, prosím.'),
            ]);
        } catch (PromoCodeWithoutRelationWithAnyProductFromCurrentCartException $exception) {
            return new JsonResponse([
                'isValidWithoutProductsInCart' => true,
                'result' => false,
                'message' => t('Promo kód nelze uplatnit na žádný produkt v košíku. Zkontrolujte ho, prosím.'),
            ]);
        } catch (AvailableForRegisteredCustomerUserOnly $exception) {
            return new JsonResponse([
                'isValidWithoutProductsInCart' => false,
                'result' => false,
                'message' => t('Promo code is available for registered customers only.'),
            ]);
        } catch (NotAvailableForCustomerUserPricingGroup $exception) {
            return new JsonResponse([
                'isValidWithoutProductsInCart' => false,
                'result' => false,
                'message' => t('Promo code is not available for your pricing group. Maybe you forgot to log in.'),
            ]);
        }
        $this->addSuccessFlash(t('Promo code added to order'));

        return new JsonResponse(['result' => true]);
    }

    public function removeAction()
    {
        $this->currentPromoCodeFacade->removeEnteredPromoCode();
        $this->addSuccessFlash(t('Promo code removed from order'));

        return $this->redirectToRoute('front_cart');
    }
}
