<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanCreate;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Payment\PaymentFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Payment\Exception\PaymentNotFoundException;
use Shopsys\FrameworkBundle\Model\Payment\Grid\PaymentGridFactory;
use Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_TRANSPORT_AND_PAYMENT)]
class PaymentController extends AdminBaseController
{
    public function __construct(
        protected readonly PaymentDataFactory $paymentDataFactory,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly PaymentFacade $paymentFacade,
        protected readonly PaymentGridFactory $paymentGridFactory,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
    ) {
    }

    #[Route(path: '/payment/new/')]
    #[CanCreate]
    public function newAction(Request $request): Response
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

        return $this->render('@ShopsysAdministration/content/payment/new.html.twig', [
            'form' => $form->createView(),
            'currencies' => $this->currencyFacade->getAllIndexedById(),
        ]);
    }

    #[Route(path: '/payment/edit/{id}', requirements: ['id' => '\d+'])]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function editAction(Request $request, int $id): Response
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

        return $this->render('@ShopsysAdministration/content/payment/edit.html.twig', [
            'form' => $form->createView(),
            'payment' => $payment,
            'currencies' => $this->currencyFacade->getAllIndexedById(),
        ]);
    }

    #[Route(path: '/payment/delete/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAction(int $id): Response
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

    #[CanView]
    public function listAction(): Response
    {
        $grid = $this->paymentGridFactory->create(AdminRoleConstant::ROLE_TRANSPORT_AND_PAYMENT);

        return $this->render('@ShopsysAdministration/content/payment/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }
}
