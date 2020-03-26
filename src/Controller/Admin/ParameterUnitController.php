<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Admin\Product\Parameter\Unit\ParameterUnitFormType;
use App\Model\Product\Parameter\Unit\ParameterUnitDataFactory;
use App\Model\Product\Parameter\Unit\ParameterUnitFacade;
use App\Model\Product\Parameter\Unit\ParameterUnitRepository;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParameterUnitController extends AdminBaseController
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider
     */
    private $breadcrumbOverrider;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\GridFactory
     */
    private $gridFactory;

    /**
     * @var \App\Model\Product\Parameter\Unit\ParameterUnitRepository
     */
    private $parameterUnitRepository;

    /**
     * @var \App\Model\Product\Parameter\Unit\ParameterUnitFacade
     */
    private $parameterUnitFacade;

    /**
     * @var \App\Model\Product\Parameter\Unit\ParameterUnitDataFactory
     */
    private $parameterUnitDataFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \App\Model\Product\Parameter\Unit\ParameterUnitRepository $parameterUnitRepository
     * @param \App\Model\Product\Parameter\Unit\ParameterUnitFacade $parameterUnitFacade
     * @param \App\Model\Product\Parameter\Unit\ParameterUnitDataFactory $parameterUnitDataFactory
     */
    public function __construct(
        Domain $domain,
        BreadcrumbOverrider $breadcrumbOverrider,
        GridFactory $gridFactory,
        ParameterUnitRepository $parameterUnitRepository,
        ParameterUnitFacade $parameterUnitFacade,
        ParameterUnitDataFactory $parameterUnitDataFactory
    ) {
        $this->domain = $domain;
        $this->breadcrumbOverrider = $breadcrumbOverrider;
        $this->gridFactory = $gridFactory;
        $this->parameterUnitRepository = $parameterUnitRepository;
        $this->parameterUnitFacade = $parameterUnitFacade;
        $this->parameterUnitDataFactory = $parameterUnitDataFactory;
    }

    /**
     * @Route("/parameter-unit/list", name="admin_parameterunit_list")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): Response
    {
        $queryBuilder = $this->parameterUnitRepository->getAllParameterUnitQueryBuilder();
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'pu.id');

        $grid = $this->gridFactory->create('parameterUnitList', $dataSource);

        $grid->addColumn('unit', 'pu.unit', t('Měrná jednotka'), true);
        $grid->addEditActionColumn('admin_parameterunit_edit', ['id' => 'pu.id']);
        $grid->setTheme('Admin/Content/ParameterUnit/listGrid.html.twig');

        $domains = $this->domain->getAll();

        return $this->render(
            'Admin/Content/ParameterUnit/list.html.twig',
            [
                'gridView' => $grid->createView(),
            ]
        );
    }

    /**
     * @Route("/parameter-unit/edit/{id}", requirements={"id" = "\d+"}, name="admin_parameterunit_edit")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, int $id): Response
    {
        $parameterUnit = $this->parameterUnitFacade->getById($id);

        $parameterUnitData = $this->parameterUnitDataFactory->createFromParameterUnit($parameterUnit);

        $form = $this->createForm(ParameterUnitFormType::class, $parameterUnitData, ['parameterUnit' => $parameterUnit]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $parameterUnit = $this->parameterUnitFacade->edit($id, $form->getData());
            $this->addSuccessFlashTwig(
                t('Měrná jednotka <strong><a href="{{ url }}">{{ parameterUnit.name }}</a></strong> je úspěšně upravena.'),
                [
                        'parameterUnit' => $parameterUnit,
                        'url' => $this->generateUrl('admin_parameterunit_edit', ['id' => $parameterUnit->getId()]),
                    ]
            );
            return $this->redirectToRoute('admin_parameterunit_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(t('Úprava měrné jednotky - %name%', ['%name%' => $parameterUnit->getName($this->domain->getLocale())]));

        return $this->render(
            'Admin/Content/ParameterUnit/edit.html.twig',
            [
                'parameterUnit' => $parameterUnit,
                'form' => $form->createView(),
            ]
        );
    }
}
