<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Form\Admin\SalesRepresentative\SalesRepresentativeFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\SalesRepresentative\Exception\SalesRepresentativeNotFoundException;
use Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeDataFactory;
use Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeFacade;
use Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeGridFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SalesRepresentativeController extends AdminBaseController
{
    protected const int DISPLAYED_CUSTOMERS_WHILE_DELETING_SALES_REPRESENTATIVE_COUNT = 10;

    /**
     * @param \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeDataFactory $salesRepresentativeDataFactory
     * @param \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeFacade $salesRepresentativeFacade
     * @param \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeGridFactory $salesRepresentativeGridFactory
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory $confirmDeleteResponseFactory
     */
    public function __construct(
        protected readonly SalesRepresentativeDataFactory $salesRepresentativeDataFactory,
        protected readonly SalesRepresentativeFacade $salesRepresentativeFacade,
        protected readonly SalesRepresentativeGridFactory $salesRepresentativeGridFactory,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly CustomerUserFacade $customerUserFacade,
        protected readonly ConfirmDeleteResponseFactory $confirmDeleteResponseFactory,
    ) {
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/sales-representative/list/')]
    public function listAction(): Response
    {
        $grid = $this->salesRepresentativeGridFactory->create();

        return $this->render('@ShopsysFramework/Admin/Content/SalesRepresentative/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/sales-representative/new/')]
    public function newAction(Request $request): Response
    {
        $salesRepresentativeData = $this->salesRepresentativeDataFactory->create();
        $form = $this->createForm(SalesRepresentativeFormType::class, $salesRepresentativeData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $salesRepresentative = $this->salesRepresentativeFacade->create($salesRepresentativeData);

            $this->addSuccessFlashTwig(
                t('Sales representative <strong><a href="{{ url }}">{{ label }}</a></strong> was created'),
                [
                    'label' => $salesRepresentative->getPresentationalLabel(),
                    'url' => $this->generateUrl('admin_salesrepresentative_edit', ['id' => $salesRepresentative->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_salesrepresentative_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/SalesRepresentative/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/sales-representative/edit/{id}', requirements: ['id' => '\d+'])]
    public function editAction(Request $request, int $id): Response
    {
        $salesRepresentative = $this->salesRepresentativeFacade->getById($id);
        $salesRepresentativeData = $this->salesRepresentativeDataFactory->createFromSalesRepresentative($salesRepresentative);

        $form = $this->createForm(SalesRepresentativeFormType::class, $salesRepresentativeData, [
            'salesRepresentative' => $salesRepresentative,
        ]);
        $form->handleRequest($request);

        $label = $salesRepresentative->getPresentationalLabel();

        if ($form->isSubmitted() && $form->isValid()) {
            $this->salesRepresentativeFacade->edit($salesRepresentative, $salesRepresentativeData);

            $this->addSuccessFlashTwig(
                t('Sales representative <strong><a href="{{ url }}">{{ label }}</a></strong> was edited'),
                [
                    'label' => $label,
                    'url' => $this->generateUrl('admin_salesrepresentative_edit', ['id' => $id]),
                ],
            );

            return $this->redirectToRoute('admin_salesrepresentative_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing sales representative - %label%', [
                '%label%' => $label,
            ]),
        );

        return $this->render('@ShopsysFramework/Admin/Content/SalesRepresentative/edit.html.twig', [
            'form' => $form->createView(),
            'salesRepresentative' => $salesRepresentative,
        ]);
    }

    /**
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/sales-representative/delete/{id}', requirements: ['id' => '\d+'])]
    public function deleteAction(int $id): Response
    {
        try {
            $salesRepresentative = $this->salesRepresentativeFacade->getById($id);

            $this->salesRepresentativeFacade->delete($id);

            $this->addSuccessFlashTwig(
                t('Sales representative <strong>{{ label }}</strong> deleted.'),
                [
                    'label' => $salesRepresentative->getPresentationalLabel(),
                ],
            );
        } catch (SalesRepresentativeNotFoundException $ex) {
            $this->addErrorFlash(t('Selected sales representative doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_salesrepresentative_list');
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/sales-representative/delete-confirm/{id}', requirements: ['id' => '\d+'])]
    public function deleteConfirmAction(int $id): Response
    {
        try {
            $salesRepresentative = $this->salesRepresentativeFacade->getById($id);
            $customersUsingThisSalesRepresentative = $this->customerUserFacade->findEmailsOfCustomerUsersUsingSalesRepresentative($id);
            $customersCount = count($customersUsingThisSalesRepresentative);

            if ($customersCount > 0) {
                $customersEnumeration = implode('<br>', array_slice($customersUsingThisSalesRepresentative, 0, self::DISPLAYED_CUSTOMERS_WHILE_DELETING_SALES_REPRESENTATIVE_COUNT));

                $message = t(
                    '{1}Sales representative "%label%" is assigned to %count% customer and will be removed if you proceed.<br><br>
                        Customer:<br>
                        %customersEnumeration%<br><br>
                        Do you really want to remove sales representative "%label%" permanently?
                        |[2,10]Sales representative "%label%" is assigned to %count% customers and will be removed if you proceed.<br><br>
                        Customers:<br>
                        %customersEnumeration%<br><br>
                        Do you really want to remove sales representative "%label%" permanently?
                        |[11,Inf]Sales representative "%label%" is assigned to %count% customers and will be removed if you proceed.<br><br>
                        Customers:<br>
                        %customersEnumeration%<br>
                        +%extraCount% more<br><br>
                        Do you really want to remove sales representative "%label%" permanently?',
                    [
                        '%label%' => $salesRepresentative->getPresentationalLabel(),
                        '%count%' => $customersCount,
                        '%extraCount%' => $customersCount - self::DISPLAYED_CUSTOMERS_WHILE_DELETING_SALES_REPRESENTATIVE_COUNT,
                        '%customersEnumeration%' => $customersEnumeration,
                    ],
                );

                return $this->confirmDeleteResponseFactory->createDeleteResponse(
                    $message,
                    'admin_salesrepresentative_delete',
                    $id,
                );
            }

            $message = t(
                'Do you really want to remove sales representative "%label%" permanently? It is not used anywhere.',
                [
                    '%label%' => $salesRepresentative->getPresentationalLabel(),
                ],
            );

            return $this->confirmDeleteResponseFactory->createDeleteResponse(
                $message,
                'admin_salesrepresentative_delete',
                $id,
            );
        } catch (SalesRepresentativeNotFoundException $ex) {
            return new Response(t('Selected sales representative doesn\'t exist.'));
        }
    }
}
