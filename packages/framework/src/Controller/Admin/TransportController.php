<?php

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Form\Admin\Transport\TransportFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportNotFoundException;
use Shopsys\FrameworkBundle\Model\Transport\Grid\TransportGridFactory;
use Shopsys\FrameworkBundle\Model\Transport\TransportDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TransportController extends AdminBaseController
{
    protected BreadcrumbOverrider $breadcrumbOverrider;

    protected CurrencyFacade $currencyFacade;

    protected TransportGridFactory $transportGridFactory;

    protected TransportDataFactoryInterface $transportDataFactory;

    protected TransportFacade $transportFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Model\Transport\Grid\TransportGridFactory $transportGridFactory
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportDataFactoryInterface $transportDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     */
    public function __construct(
        TransportFacade $transportFacade,
        TransportGridFactory $transportGridFactory,
        TransportDataFactoryInterface $transportDataFactory,
        CurrencyFacade $currencyFacade,
        BreadcrumbOverrider $breadcrumbOverrider
    ) {
        $this->transportFacade = $transportFacade;
        $this->transportGridFactory = $transportGridFactory;
        $this->transportDataFactory = $transportDataFactory;
        $this->currencyFacade = $currencyFacade;
        $this->breadcrumbOverrider = $breadcrumbOverrider;
    }

    /**
     * @Route("/transport/new/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function newAction(Request $request)
    {
        $transportData = $this->transportDataFactory->create();

        $form = $this->createForm(TransportFormType::class, $transportData, [
            'transport' => null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $transport = $this->transportFacade->create($transportData);

            $this->addSuccessFlashTwig(
                t('Shipping <strong><a href="{{ url }}">{{ name }}</a></strong> created'),
                [
                    'name' => $transport->getName(),
                    'url' => $this->generateUrl('admin_transport_edit', ['id' => $transport->getId()]),
                ]
            );
            return $this->redirectToRoute('admin_transportandpayment_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/Transport/new.html.twig', [
            'form' => $form->createView(),
            'currencies' => $this->currencyFacade->getAllIndexedById(),
        ]);
    }

    /**
     * @Route("/transport/edit/{id}", requirements={"id" = "\d+"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     */
    public function editAction(Request $request, $id)
    {
        $transport = $this->transportFacade->getById($id);
        $transportData = $this->transportDataFactory->createFromTransport($transport);

        $form = $this->createForm(TransportFormType::class, $transportData, [
            'transport' => $transport,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->transportFacade->edit($transport, $transportData);

            $this->addSuccessFlashTwig(
                t('Shipping <strong><a href="{{ url }}">{{ name }}</a></strong> was modified'),
                [
                    'name' => $transport->getName(),
                    'url' => $this->generateUrl('admin_transport_edit', ['id' => $transport->getId()]),
                ]
            );
            return $this->redirectToRoute('admin_transportandpayment_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing shipping - %name%', ['%name%' => $transport->getName()])
        );

        return $this->render('@ShopsysFramework/Admin/Content/Transport/edit.html.twig', [
            'form' => $form->createView(),
            'transport' => $transport,
            'currencies' => $this->currencyFacade->getAllIndexedById(),
        ]);
    }

    /**
     * @Route("/transport/delete/{id}", requirements={"id" = "\d+"})
     * @CsrfProtection
     * @param int $id
     */
    public function deleteAction($id)
    {
        try {
            $transportName = $this->transportFacade->getById($id)->getName();

            $this->transportFacade->deleteById($id);

            $this->addSuccessFlashTwig(
                t('Shipping <strong>{{ name }}</strong> deleted'),
                [
                    'name' => $transportName,
                ]
            );
        } catch (TransportNotFoundException $ex) {
            $this->addErrorFlash(t('Selected shipping doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_transportandpayment_list');
    }

    public function listAction()
    {
        $grid = $this->transportGridFactory->create();

        return $this->render('@ShopsysFramework/Admin/Content/Transport/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }
}
