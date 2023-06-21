<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Admin\TransportTypeFormType;
use App\Model\Transport\Type\TransportTypeDataFactory;
use App\Model\Transport\Type\TransportTypeFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TransportTypeController extends AdminBaseController
{
    /**
     * @param \App\Model\Transport\Type\TransportTypeFacade $transportTypeFacade
     * @param \App\Model\Transport\Type\TransportTypeDataFactory $transportTypeDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     */
    public function __construct(
        protected TransportTypeFacade $transportTypeFacade,
        protected TransportTypeDataFactory $transportTypeDataFactory,
        protected GridFactory $gridFactory,
        protected Domain $domain,
        protected BreadcrumbOverrider $breadcrumbOverrider,
    ) {
    }

    /**
     * @Route("/transport-type/list/")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): Response
    {
        $queryBuilder = $this->transportTypeFacade->getLocalisedQueryBuilder($this->domain->getLocale());
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'tt.id');

        $grid = $this->gridFactory->create('TransportTypeList', $dataSource);

        $grid->addColumn('code', 'tt.code', t('Code'));
        $grid->addColumn('name', 'ttt.name', t('Name'));

        $grid->addEditActionColumn('admin_transporttype_edit', ['id' => 'tt.id']);

        $grid->setTheme('Admin/Content/TransportType/listGrid.html.twig');

        return $this->render('Admin/Content/TransportType/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @Route("/transport-type/edit/{id}", requirements={"id" = "\d+"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, int $id): Response
    {
        $transportType = $this->transportTypeFacade->getById($id);
        $transportTypeData = $this->transportTypeDataFactory->createFromTransportType($transportType);

        $form = $this->createForm(TransportTypeFormType::class, $transportTypeData, [
            'transport_type' => $transportType,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $transportType = $this->transportTypeFacade->edit($transportType, $transportTypeData);

            $this->addSuccessFlashTwig(
                t('Transport type <strong><a href="{{ url }}">{{ code }}</a></strong> modified'),
                [
                    'url' => $this->generateUrl('admin_transporttype_edit', ['id' => $transportType->getId()]),
                    'code' => $transportType->getCode(),
                ],
            );

            return $this->redirectToRoute('admin_transporttype_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(t('Editing transport type - %code%', ['%code%' => $transportType->getCode()]));

        return $this->render('Admin/Content/TransportType/edit.html.twig', [
            'form' => $form->createView(),
            'transportType' => $transportType,
        ]);
    }
}
