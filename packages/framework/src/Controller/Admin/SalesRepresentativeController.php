<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Form\Admin\SalesRepresentative\SalesRepresentativeFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\SalesRepresentative\Exception\SalesRepresentativeNotFoundException;
use Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SalesRepresentativeController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeDataFactoryInterface $salesRepresentativeDataFactory
     * @param \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeFacade $salesRepresentativeFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     */
    public function __construct(
        protected readonly SalesRepresentativeDataFactoryInterface $salesRepresentativeDataFactory,
        protected readonly SalesRepresentativeFacade $salesRepresentativeFacade,
        protected readonly GridFactory $gridFactory,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
    ) {
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/sales-representative/list/')]
    public function listAction(): Response
    {
        $queryBuilder = $this->salesRepresentativeFacade->getAllQueryBuilder();
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'sr.id');

        $grid = $this->gridFactory->create('salesRepresentativesList', $dataSource);
        $grid->setDefaultOrder('name');

        $grid->addColumn('firstName', 'sr.firstName', t('First name'), true);
        $grid->addColumn('lastName', 'sr.lastName', t('Last name'), true);
        $grid->addColumn('telephone', 'sr.telephone', t('Telephone'), true);
        $grid->addColumn('email', 'sr.email', t('E-mail'), true);

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_salesrepresentative_edit', ['id' => 'sr.id']);
        $grid->addDeleteActionColumn('admin_salesrepresentative_delete', ['id' => 'sr.id'])
            ->setConfirmMessage(t('Do you really want to remove this sales representative?'));

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
                t('Sales representative <strong><a href="{{ url }}">{{ name }}</a></strong> was created'),
                [
                    'name' => $salesRepresentative->getFirstName() . ' ' . $salesRepresentative->getLastName(),
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

        if ($form->isSubmitted() && $form->isValid()) {
            $this->salesRepresentativeFacade->edit($salesRepresentative, $salesRepresentativeData);

            $this->addSuccessFlashTwig(
                t('Sales representative <strong><a href="{{ url }}">{{ name }}</a></strong> was edited'),
                [
                    'name' => $salesRepresentativeData->firstName . ' ' . $salesRepresentativeData->lastName,
                    'url' => $this->generateUrl('admin_salesrepresentative_edit', ['id' => $id]),
                ],
            );

            return $this->redirectToRoute('admin_salesrepresentative_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing sales representative - %name%', ['%name%' => $salesRepresentative->getFirstName() . ' ' . $salesRepresentative->getLastName()]),
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
        $customersUsingThisSalesRepresentative = $this->salesRepresentativeFacade->findCustomersWithSalesRepresentative($id);

        if (count($customersUsingThisSalesRepresentative) !== 0) {
            $this->addErrorFlashTwig(
                t('Sales representative cannot be deleted, because some customers are using it: {{ customers }}'),
                [
                    'customers' => implode(', ', $customersUsingThisSalesRepresentative),
                ],
            );

            return $this->redirectToRoute('admin_salesrepresentative_list');
        }

        try {
            $firstName = $this->salesRepresentativeFacade->getById($id)->getFirstName();
            $lastName = $this->salesRepresentativeFacade->getById($id)->getLastName();

            $this->salesRepresentativeFacade->delete($id);
            $this->addSuccessFlashTwig(
                t('Sales representative <strong>{{ name }}</strong> deleted.'),
                [
                    'name' => $firstName . ' ' . $lastName,
                ],
            );
        } catch (SalesRepresentativeNotFoundException $ex) {
            $this->addErrorFlash(t('Selected sales representative doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_salesrepresentative_list');
    }
}
