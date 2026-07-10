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
use Shopsys\FrameworkBundle\Form\Admin\Transport\TransportFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Transport\Exception\EmailTransportCannotBeDeletedException;
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportNotFoundException;
use Shopsys\FrameworkBundle\Model\Transport\Grid\TransportGridFactory;
use Shopsys\FrameworkBundle\Model\Transport\TransportDataFactory;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportTypeEnum;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_TRANSPORT_AND_PAYMENT)]
class TransportController extends AdminBaseController
{
    public function __construct(
        protected readonly TransportFacade $transportFacade,
        protected readonly TransportGridFactory $transportGridFactory,
        protected readonly TransportDataFactory $transportDataFactory,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
    ) {
    }

    #[Route(path: '/transport/new/')]
    #[CanCreate]
    public function newAction(Request $request): Response
    {
        $transportData = $this->transportDataFactory->create();

        $form = $this->createForm(TransportFormType::class, $transportData, [
            'transport' => null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($transportData->type === TransportTypeEnum::TYPE_EMAIL) {
                $this->addErrorFlash(t('Shipping of type Email cannot be created, exactly one must always exist.'));

                return $this->redirectToRoute('admin_transport_new');
            }

            $transport = $this->transportFacade->create($transportData);

            $this->addSuccessFlashTwig(
                t('Shipping <strong><a href="{{ url }}">{{ name }}</a></strong> created'),
                [
                    'name' => $transport->getName(),
                    'url' => $this->generateUrl('admin_transport_edit', ['id' => $transport->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_transportandpayment_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAdministration/content/transport/new.html.twig', [
            'form' => $form->createView(),
            'currencies' => $this->currencyFacade->getAllIndexedById(),
        ]);
    }

    #[Route(path: '/transport/edit/{id}', requirements: ['id' => '\d+'])]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function editAction(Request $request, int $id): Response
    {
        $transport = $this->transportFacade->getById($id);
        $transportData = $this->transportDataFactory->createFromTransport($transport);

        $form = $this->createForm(TransportFormType::class, $transportData, [
            'transport' => $transport,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $isEmailTypeSubmitted = $transportData->type === TransportTypeEnum::TYPE_EMAIL;

            if ($transport->isEmailType() !== $isEmailTypeSubmitted) {
                $this->addErrorFlash(t('Shipping type cannot be changed from or to type Email, exactly one shipping of type Email must always exist.'));

                return $this->redirectToRoute('admin_transport_edit', ['id' => $transport->getId()]);
            }

            $this->transportFacade->edit($transport, $transportData);

            $this->addSuccessFlashTwig(
                t('Shipping <strong><a href="{{ url }}">{{ name }}</a></strong> was modified'),
                [
                    'name' => $transport->getName(),
                    'url' => $this->generateUrl('admin_transport_edit', ['id' => $transport->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_transportandpayment_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing shipping - %name%', ['%name%' => $transport->getName()]),
        );

        return $this->render('@ShopsysAdministration/content/transport/edit.html.twig', [
            'form' => $form->createView(),
            'transport' => $transport,
            'currencies' => $this->currencyFacade->getAllIndexedById(),
        ]);
    }

    #[Route(path: '/transport/delete/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAction(int $id): Response
    {
        try {
            $transportName = $this->transportFacade->getById($id)->getName();

            $this->transportFacade->deleteById($id);

            $this->addSuccessFlashTwig(
                t('Shipping <strong>{{ name }}</strong> deleted'),
                [
                    'name' => $transportName,
                ],
            );
        } catch (TransportNotFoundException $ex) {
            $this->addErrorFlash(t('Selected shipping doesn\'t exist.'));
        } catch (EmailTransportCannotBeDeletedException $ex) {
            $this->addErrorFlash(t('Shipping of type Email cannot be deleted, exactly one must always exist.'));
        }

        return $this->redirectToRoute('admin_transportandpayment_list');
    }

    #[CanView]
    public function listAction(): Response
    {
        $grid = $this->transportGridFactory->create(AdminRoleConstant::ROLE_TRANSPORT_AND_PAYMENT);

        return $this->render('@ShopsysAdministration/content/transport/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }
}
