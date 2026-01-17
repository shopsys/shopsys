<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\TransportAndPayment\FreeTransportAndPaymentPriceLimitsFormType;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TransportAndPaymentController extends AdminBaseController
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly PricingSetting $pricingSetting,
    ) {
    }

    #[Route(path: '/transport-and-payment/list/')]
    #[CanView(AdminRoleConstant::ROLE_TRANSPORT_AND_PAYMENT)]
    public function listAction(): Response
    {
        return $this->render('@ShopsysAdministration/content/transportAndPayment/list.html.twig');
    }

    #[Route(path: '/transport-and-payment/free-transport-and-payment-limit/')]
    #[CanEdit(AdminRoleConstant::ROLE_FREE_TRANSPORT_AND_PAYMENT, methods: [HttpMethod::POST])]
    #[CanView(AdminRoleConstant::ROLE_FREE_TRANSPORT_AND_PAYMENT, methods: [HttpMethod::GET])]
    public function freeTransportAndPaymentLimitAction(Request $request): Response
    {
        $formData = [];

        foreach ($this->domain->getAll() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $freeTransportAndPaymentPriceLimit = $this->pricingSetting->getFreeTransportAndPaymentPriceLimit(
                $domainId,
            );

            $formData[FreeTransportAndPaymentPriceLimitsFormType::DOMAINS_SUBFORM_NAME][$domainId] = [
                FreeTransportAndPaymentPriceLimitsFormType::FIELD_ENABLED => $freeTransportAndPaymentPriceLimit !== null,
                FreeTransportAndPaymentPriceLimitsFormType::FIELD_PRICE_LIMIT => $freeTransportAndPaymentPriceLimit,
            ];
        }

        $form = $this->createForm(FreeTransportAndPaymentPriceLimitsFormType::class, $formData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $subformData = $formData[FreeTransportAndPaymentPriceLimitsFormType::DOMAINS_SUBFORM_NAME];

            foreach ($this->domain->getAll() as $domainConfig) {
                $domainId = $domainConfig->getId();

                if ($subformData[$domainId][FreeTransportAndPaymentPriceLimitsFormType::FIELD_ENABLED]) {
                    $priceLimit = $subformData[$domainId][FreeTransportAndPaymentPriceLimitsFormType::FIELD_PRICE_LIMIT];
                } else {
                    $priceLimit = null;
                }

                $this->pricingSetting->setFreeTransportAndPaymentPriceLimit($domainId, $priceLimit);
            }

            $this->addSuccessFlash(t('Free shipping and payment settings saved'));

            return $this->redirectToRoute('admin_transportandpayment_freetransportandpaymentlimit');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        return $this->render(
            '@ShopsysAdministration/content/transportAndPayment/freeTransportAndPaymentLimitSetting.html.twig',
            [
                'form' => $form->createView(),
                'domain' => $this->domain,
            ],
        );
    }
}
