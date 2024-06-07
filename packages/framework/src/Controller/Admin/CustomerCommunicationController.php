<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Form\Admin\CustomerCommunication\CustomerUserCommunicationFormType;
use Shopsys\FrameworkBundle\Model\Order\ContentPage\OrderContentPageFacade;
use Shopsys\FrameworkBundle\Model\Order\ContentPage\OrderContentPageSettingFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CustomerCommunicationController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\ContentPage\OrderContentPageSettingFacade $orderContentPageSettingFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(
        protected readonly OrderContentPageSettingFacade $orderContentPageSettingFacade,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/customer-communication/order-submitted/')]
    public function orderSubmittedAction(Request $request): Response
    {
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();

        $form = $this->createForm(
            CustomerUserCommunicationFormType::class,
            [
                CustomerUserCommunicationFormType::ORDER_SENT_CONTENT_FIELD_NAME => $this->orderContentPageSettingFacade->getOrderSentPageContent($domainId),
                CustomerUserCommunicationFormType::PAYMENT_SUCCESSFUL_CONTENT_FIELD_NAME => $this->orderContentPageSettingFacade->getPaymentSuccessfulPageContent($domainId),
                CustomerUserCommunicationFormType::PAYMENT_FAILED_CONTENT_FIELD_NAME => $this->orderContentPageSettingFacade->getPaymentFailedPageContent($domainId),
            ],
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $this->orderContentPageSettingFacade->setOrderSentPageContent($formData[CustomerUserCommunicationFormType::ORDER_SENT_CONTENT_FIELD_NAME], $domainId);
            $this->orderContentPageSettingFacade->setPaymentSuccessfulPageContent($formData[CustomerUserCommunicationFormType::PAYMENT_SUCCESSFUL_CONTENT_FIELD_NAME], $domainId);
            $this->orderContentPageSettingFacade->setPaymentFailedPageContent($formData[CustomerUserCommunicationFormType::PAYMENT_FAILED_CONTENT_FIELD_NAME], $domainId);

            $this->addSuccessFlash(t('Order confirmation page content modified'));

            return $this->redirectToRoute($request->attributes->get('_route'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/CustomerCommunication/orderSubmitted.html.twig', [
            'form' => $form->createView(),
            'VARIABLE_TRANSPORT_INSTRUCTIONS' => OrderContentPageFacade::VARIABLE_TRANSPORT_INSTRUCTIONS,
            'VARIABLE_PAYMENT_INSTRUCTIONS' => OrderContentPageFacade::VARIABLE_PAYMENT_INSTRUCTIONS,
            'VARIABLE_ORDER_DETAIL_URL' => OrderContentPageFacade::VARIABLE_ORDER_DETAIL_URL,
            'VARIABLE_NUMBER' => OrderContentPageFacade::VARIABLE_NUMBER,
        ]);
    }
}
