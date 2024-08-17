<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Form\Admin\Complaint\ComplaintFormType;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\AdvancedSearchComplaint\AdvancedSearchComplaintFacade;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintDataFactory;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ComplaintController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintFacade $complaintFacade
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade $administratorGridFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintDataFactory $complaintDataFactory
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearchComplaint\AdvancedSearchComplaintFacade $advancedSearchComplaintFacade
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly GridFactory $gridFactory,
        protected readonly ComplaintFacade $complaintFacade,
        protected readonly AdministratorGridFacade $administratorGridFacade,
        protected readonly AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly ComplaintDataFactory $complaintDataFactory,
        protected readonly AdvancedSearchComplaintFacade $advancedSearchComplaintFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/complaint/list/')]
    public function listAction(Request $request): Response
    {
        $domainFilterNamespace = 'complaints';

        $selectedDomainId = $this->adminDomainFilterTabsFacade->getSelectedDomainId($domainFilterNamespace);

        $advancedSearchForm = $this->advancedSearchComplaintFacade->createAdvancedSearchComplaintForm($request);
        $advancedSearchData = $advancedSearchForm->getData();

        $quickSearchForm = $this->createForm(QuickSearchFormType::class, new QuickSearchFormData());
        $quickSearchForm->handleRequest($request);

        $isAdvancedSearchFormSubmitted = $this->advancedSearchComplaintFacade->isAdvancedSearchComplaintFormSubmitted(
            $request,
        );

        if ($isAdvancedSearchFormSubmitted) {
            $queryBuilder = $this->advancedSearchComplaintFacade->getQueryBuilderByAdvancedSearchData(
                $advancedSearchData,
            );
        } else {
            $queryBuilder = $this->advancedSearchComplaintFacade->getComplaintListQueryBuilderByQuickSearchData($quickSearchForm->getData());
        }

        $dataSource = new QueryBuilderDataSource($queryBuilder, 'cmp.id');

        if ($selectedDomainId !== null) {
            $queryBuilder
                ->andWhere('cmp.domainId = :selectedDomainId')
                ->setParameter('selectedDomainId', $selectedDomainId);
        }

        return $this->render('@ShopsysFramework/Admin/Content/Complaint/list.html.twig', [
            'gridView' => $this->createGrid($dataSource)->createView(),
            'domains' => $this->domain->getAll(),
            'domainFilterNamespace' => $domainFilterNamespace,
            'isAdvancedSearchFormSubmitted' => $isAdvancedSearchFormSubmitted,
            'quickSearchForm' => $quickSearchForm->createView(),
            'advancedSearchForm' => $advancedSearchForm->createView(),
        ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface $dataSource
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    protected function createGrid(DataSourceInterface $dataSource): Grid
    {
        $grid = $this->gridFactory->create('complaintList', $dataSource);

        $grid->enablePaging();
        $grid->setDefaultOrder('created_at', DataSourceInterface::ORDER_DESC);

        $grid->addColumn('number', 'cmp.number', t('Complaint Nr.'), true);
        $grid->addColumn('created_at', 'cmp.createdAt', t('Created'), true);
        $grid->addColumn('customer_name', 'customerName', t('Customer'), true);

        if ($this->domain->isMultidomain()) {
            $grid->addColumn('domain_id', 'cmp.domainId', t('Domain'), true);
        }
        $grid->addColumn('status_name', 'statusName', t('Status'), true);

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_complaint_edit', ['id' => 'cmp.id']);

        $grid->setTheme('@ShopsysFramework/Admin/Content/Complaint/listGrid.html.twig');

        $this->administratorGridFacade->restoreAndRememberGridLimit($this->getCurrentAdministrator(), $grid);

        return $grid;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/complaint/edit/{id}', requirements: ['id' => '\d+'])]
    public function editAction(Request $request, int $id): Response
    {
        $complaint = $this->complaintFacade->getById($id);
        $complaintData = $this->complaintDataFactory->createFromComplaint($complaint);

        $form = $this->createForm(ComplaintFormType::class, $complaintData, ['complaint' => $complaint]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->complaintFacade->edit($id, $complaintData);

            $this->addSuccessFlashTwig(
                t('Complaint Nr. <strong><a href="{{ url }}">{{ number }}</a></strong> modified'),
                [
                    'number' => $complaint->getNumber(),
                    'url' => $this->generateUrl('admin_complaint_edit', ['id' => $complaint->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_complaint_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing complaint - Nr. %number%', ['%number%' => $complaint->getNumber()]),
        );


        return $this->render('@ShopsysFramework/Admin/Content/Complaint/edit.html.twig', [
            'form' => $form->createView(),
            'complaint' => $complaint,
            'domains' => $this->domain->getAll(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/complaint/get-advanced-search-rule-form/', methods: ['post'])]
    public function getRuleFormAction(Request $request): Response
    {
        $ruleForm = $this->advancedSearchComplaintFacade->createRuleForm(
            $request->get('filterName'),
            $request->get('newIndex'),
        );

        return $this->render('@ShopsysFramework/Admin/Content/Complaint/AdvancedSearch/ruleForm.html.twig', [
            'rulesForm' => $ruleForm->createView(),
        ]);
    }
}
