<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\CustomerCommunication\CustomerUserCommunicationFormType;
use Shopsys\FrameworkBundle\Model\Order\ContentPage\OrderContentPageFacade;
use Shopsys\FrameworkBundle\Model\Order\ContentPage\OrderContentPageSettingFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_ORDER_SUBMITTED)]
class CustomerCommunicationController extends AdminBaseController
{
    public function __construct(
        protected readonly OrderContentPageSettingFacade $orderContentPageSettingFacade,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    #[Route(path: '/customer-communication/order-submitted/')]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function orderSubmittedAction(Request $request): Response
    {
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();

        $form = $this->createForm(
            CustomerUserCommunicationFormType::class,
            [
                CustomerUserCommunicationFormType::ORDER_SENT_CONTENT_FIELD_NAME => $this->orderContentPageSettingFacade->getOrderSentPageContent($domainId),
                CustomerUserCommunicationFormType::PAYMENT_SUCCESSFUL_CONTENT_FIELD_NAME => $this->orderContentPageSettingFacade->getPaymentSuccessfulPageContent($domainId),
                CustomerUserCommunicationFormType::PAYMENT_FAILED_CONTENT_FIELD_NAME => $this->orderContentPageSettingFacade->getPaymentFailedPageContent($domainId),
                CustomerUserCommunicationFormType::PAYMENT_IN_PROCESS_CONTENT_FIELD_NAME => $this->orderContentPageSettingFacade->getPaymentInProcessPageContent($domainId),
            ],
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $this->orderContentPageSettingFacade->setOrderSentPageContent($formData[CustomerUserCommunicationFormType::ORDER_SENT_CONTENT_FIELD_NAME], $domainId);
            $this->orderContentPageSettingFacade->setPaymentSuccessfulPageContent($formData[CustomerUserCommunicationFormType::PAYMENT_SUCCESSFUL_CONTENT_FIELD_NAME], $domainId);
            $this->orderContentPageSettingFacade->setPaymentFailedPageContent($formData[CustomerUserCommunicationFormType::PAYMENT_FAILED_CONTENT_FIELD_NAME], $domainId);
            $this->orderContentPageSettingFacade->setPaymentInProcessPageContent($formData[CustomerUserCommunicationFormType::PAYMENT_IN_PROCESS_CONTENT_FIELD_NAME], $domainId);

            $this->addSuccessFlash(t('Order confirmation page content modified'));

            return $this->redirectToRoute($request->attributes->get('_route'));
        }

        return $this->render('@ShopsysAdministration/content/customerCommunication/orderSubmitted.html.twig', [
            'form' => $form->createView(),
            'VARIABLE_TRANSPORT_INSTRUCTIONS' => OrderContentPageFacade::VARIABLE_TRANSPORT_INSTRUCTIONS,
            'VARIABLE_PAYMENT_INSTRUCTIONS' => OrderContentPageFacade::VARIABLE_PAYMENT_INSTRUCTIONS,
            'VARIABLE_ORDER_DETAIL_URL' => OrderContentPageFacade::VARIABLE_ORDER_DETAIL_URL,
            'VARIABLE_NUMBER' => OrderContentPageFacade::VARIABLE_NUMBER,
        ]);
    }
}
