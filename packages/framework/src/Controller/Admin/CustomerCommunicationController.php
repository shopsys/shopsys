<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Form\Admin\CustomerCommunication\CustomerUserCommunicationFormType;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CustomerCommunicationController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(
        protected readonly Setting $setting,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    /**
     * @Route("/customer-communication/order-submitted/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function orderSubmittedAction(Request $request)
    {
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $orderSentPageContent = $this->setting->getForDomain(Setting::ORDER_SENT_PAGE_CONTENT, $domainId);

        $form = $this->createForm(CustomerUserCommunicationFormType::class, ['content' => $orderSentPageContent]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $this->setting->setForDomain(Setting::ORDER_SENT_PAGE_CONTENT, $formData['content'], $domainId);

            $this->addSuccessFlash(t('Order confirmation page content modified'));

            return $this->redirectToRoute('admin_customercommunication_ordersubmitted');
        }

        return $this->render('@ShopsysFramework/Admin/Content/CustomerCommunication/orderSubmitted.html.twig', [
            'form' => $form->createView(),
            'VARIABLE_TRANSPORT_INSTRUCTIONS' => OrderFacade::VARIABLE_TRANSPORT_INSTRUCTIONS,
            'VARIABLE_PAYMENT_INSTRUCTIONS' => OrderFacade::VARIABLE_PAYMENT_INSTRUCTIONS,
            'VARIABLE_ORDER_DETAIL_URL' => OrderFacade::VARIABLE_ORDER_DETAIL_URL,
            'VARIABLE_NUMBER' => OrderFacade::VARIABLE_NUMBER,
        ]);
    }
}
