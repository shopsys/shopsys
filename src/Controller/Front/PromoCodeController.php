<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Component\PromoCode\HttpBasicAuthOrByIpAccessAuthenticator;
use App\Model\Order\PromoCode\CurrentPromoCodeFacade;
use App\Model\Order\PromoCode\Exception\NoLongerValidPromoCodeDateTimeException;
use App\Model\Order\PromoCode\Exception\NotYetValidPromoCodeDateTimeException;
use App\Model\Order\PromoCode\Exception\PromoCodeWithoutRelationWithAnyProductFromCurrentCartException;
use App\Model\Order\PromoCode\PromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\InvalidPromoCodeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PromoCodeController extends FrontBaseController
{
    public const PROMO_CODE_PARAMETER = 'code';

    /**
     * @var \App\Model\Order\PromoCode\CurrentPromoCodeFacade|\App\Model\Order\PromoCode\CurrentPromoCodeFacade
     */
    private $currentPromoCodeFacade;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeFacade
     */
    private $promoCodeFacade;

    /**
     * @var \App\Component\PromoCode\HttpBasicAuthOrByIpAccessAuthenticator
     */
    private $httpBasicAuthOrByIpAccessAuthenticator;

    /**
     * @param \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     * @param \App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     * @param \App\Component\PromoCode\HttpBasicAuthOrByIpAccessAuthenticator $httpBasicAuthOrByIpAccessAuthenticator
     */
    public function __construct(
        CurrentPromoCodeFacade $currentPromoCodeFacade,
        PromoCodeFacade $promoCodeFacade,
        HttpBasicAuthOrByIpAccessAuthenticator $httpBasicAuthOrByIpAccessAuthenticator
    ) {
        $this->currentPromoCodeFacade = $currentPromoCodeFacade;
        $this->promoCodeFacade = $promoCodeFacade;
        $this->httpBasicAuthOrByIpAccessAuthenticator = $httpBasicAuthOrByIpAccessAuthenticator;
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

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexManagementAction(Request $request): Response
    {
        $verifyAccess = $this->httpBasicAuthOrByIpAccessAuthenticator->verifyAccess($request);

        if ($verifyAccess === false) {
            return $this->getAuthResponse();
        }
        return $this->render('Front/Content/Order/PromoCode/promoCodeManagementIndex.html.twig');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    public function findAndValidateAction(Request $request): ?Response
    {
        $verifyAccess = $this->httpBasicAuthOrByIpAccessAuthenticator->verifyAccess($request);
        if ($verifyAccess === false) {
            return $this->getAuthResponse();
        }
        $applyCodeResponse = json_decode($this->applyAction($request)->getContent(), true);
        $promoCodeCode = $request->get('code');
        $promoCode = null;

        if (($applyCodeResponse['result'] === true || $applyCodeResponse['isValidWithoutProductsInCart'] === true) && $request->isXmlHttpRequest() && $promoCodeCode !== null) {
            $promoCode = $this->promoCodeFacade->findPromoCodeByCode($promoCodeCode);
        }

        return $this->render('Front/Content/Order/PromoCode/promoCodeValidationResult.html.twig', [
            'message' => (isset($applyCodeResponse['message'])) ? $applyCodeResponse['message'] : '',
            'promoCode' => $promoCode,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function usePromoCodeAction(Request $request): Response
    {
        $verifyAccess = $this->httpBasicAuthOrByIpAccessAuthenticator->verifyAccess($request);
        if ($verifyAccess === false) {
            return $this->getAuthResponse();
        }

        $promoCodeId = $request->get('promoCodeId');
        $promoCode = $this->promoCodeFacade->getById((int)$promoCodeId);

        if ($promoCode !== null) {
            try {
                $this->promoCodeFacade->decreaseRemainingUses($promoCode);
                return new JsonResponse([
                    'result' => true,
                    'message' => t('Slevový kupón byl použit'),
                ]);
            } catch (\Exception $e) {
                return new JsonResponse([
                    'result' => false,
                    'message' => t('Bohužel došlo k chybě, zkuste to prosím znovu.'),
                ]);
            }
        }
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function getAuthResponse(): Response
    {
        $authResponse = new Response();
        $authResponse->headers->set('WWW-Authenticate', 'Basic realm=""');
        $authResponse->headers->set('status', '401 Unauthorized');
        return $authResponse;
    }
}
