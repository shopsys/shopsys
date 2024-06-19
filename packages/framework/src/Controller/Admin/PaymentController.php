<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Form\Admin\Payment\PaymentFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Payment\Exception\PaymentNotFoundException;
use Shopsys\FrameworkBundle\Model\Payment\Grid\PaymentGridFactory;
use Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactoryInterface $paymentDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Model\Payment\Grid\PaymentGridFactory $paymentGridFactory
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     */
    public function __construct(
        protected readonly PaymentDataFactoryInterface $paymentDataFactory,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly PaymentFacade $paymentFacade,
        protected readonly PaymentGridFactory $paymentGridFactory,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    #[Route(path: '/payment/new/')]
    public function newAction(Request $request)
    {
        $paymentData = $this->paymentDataFactory->create();

        $form = $this->createForm(PaymentFormType::class, $paymentData, [
            'payment' => null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $payment = $this->paymentFacade->create($paymentData);

            $this->addSuccessFlashTwig(
                t('Payment <strong><a href="{{ url }}">{{ name }}</a></strong> created'),
                [
                    'name' => $payment->getName(),
                    'url' => $this->generateUrl('admin_payment_edit', ['id' => $payment->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_transportandpayment_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/Payment/new.html.twig', [
            'form' => $form->createView(),
            'currencies' => $this->currencyFacade->getAllIndexedById(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     */
    #[Route(path: '/payment/edit/{id}', requirements: ['id' => '\d+'])]
    public function editAction(Request $request, $id)
    {
        $payment = $this->paymentFacade->getById($id);
        $paymentData = $this->paymentDataFactory->createFromPayment($payment);

        $form = $this->createForm(PaymentFormType::class, $paymentData, [
            'payment' => $payment,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->paymentFacade->edit($payment, $paymentData);

            $this->addSuccessFlashTwig(
                t('Payment <strong><a href="{{ url }}">{{ name }}</a></strong> modified'),
                [
                    'name' => $payment->getName(),
                    'url' => $this->generateUrl('admin_payment_edit', ['id' => $payment->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_transportandpayment_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(t('Editing payment - %name%', ['%name%' => $payment->getName()]));

        return $this->render('@ShopsysFramework/Admin/Content/Payment/edit.html.twig', [
            'form' => $form->createView(),
            'payment' => $payment,
            'currencies' => $this->currencyFacade->getAllIndexedById(),
        ]);
    }

    /**
     * @CsrfProtection
     * @param int $id
     */
    #[Route(path: '/payment/delete/{id}', requirements: ['id' => '\d+'])]
    public function deleteAction($id)
    {
        try {
            $paymentName = $this->paymentFacade->getById($id)->getName();

            $this->paymentFacade->deleteById($id);

            $this->addSuccessFlashTwig(
                t('Payment <strong>{{ name }}</strong> deleted'),
                [
                    'name' => $paymentName,
                ],
            );
        } catch (PaymentNotFoundException $ex) {
            $this->addErrorFlash(t('Selected payment doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_transportandpayment_list');
    }

    public function listAction()
    {
        $grid = $this->paymentGridFactory->create();

        return $this->render('@ShopsysFramework/Admin/Content/Payment/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }
}
