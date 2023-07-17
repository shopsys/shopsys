<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Admin\Product\Parameter\Value\ParameterValueFormType;
use App\Model\Product\Parameter\Parameter;
use App\Model\Product\Parameter\ParameterFacade;
use App\Model\Product\Parameter\ParameterRepository;
use App\Model\Product\Parameter\ParameterValueDataFactory;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParameterValueController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \App\Model\Product\Parameter\ParameterValueDataFactory $parameterValueDataFactory
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     */
    public function __construct(
        private GridFactory $gridFactory,
        private ParameterRepository $parameterRepository,
        private AdminDomainTabsFacade $adminDomainTabsFacade,
        private ParameterFacade $parameterFacade,
        private ParameterValueDataFactory $parameterValueDataFactory,
        private BreadcrumbOverrider $breadcrumbOverrider,
    ) {
    }

    /**
     * @Route("/parameter-value/list", name="admin_parametervalue_list")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): Response
    {
        $domainConfig = $this->adminDomainTabsFacade->getSelectedDomainConfig();

        $queryBuilder = $this->parameterRepository->getQueryBuilderParameterValuesUsedByProductsByLocaleAndType($domainConfig->getLocale(), Parameter::PARAMETER_TYPE_COLOR);
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'pv.id');

        $grid = $this->gridFactory->create('parameterValues', $dataSource);

        $grid->addColumn('text', 'pv.text', t('Parameter value'));
        $grid->addColumn('rgbHex', 'pv.rgbHex', t('RGB Hex'));
        $grid->addEditActionColumn('admin_parametervalue_edit', ['id' => 'pv.id']);
        $grid->setTheme('Admin/Content/ParameterValue/listGrid.html.twig');

        return $this->render(
            'Admin/Content/ParameterValue/list.html.twig',
            [
                'gridView' => $grid->createView(),
            ],
        );
    }

    /**
     * @Route("/parameter-value/edit/{id}", requirements={"id" = "\d+"}, name="admin_parametervalue_edit")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, int $id): Response
    {
        $parameterValue = $this->parameterFacade->getParameterValueById($id);
        $parameterValueData = $this->parameterValueDataFactory->createFromParameterValue($parameterValue);

        $form = $this->createForm(ParameterValueFormType::class, $parameterValueData, ['entity' => $parameterValue]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $parameterValue = $this->parameterFacade->editParameterValue($id, $form->getData());
            $this->addSuccessFlashTwig(
                t('Parameter value <strong><a href="{{ url }}">{{ parameterValue.text }}</a></strong> modified.'),
                [
                    'parameterValue' => $parameterValue,
                    'url' => $this->generateUrl('admin_parametervalue_edit', ['id' => $parameterValue->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_parametervalue_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(t('Editing pararameter value of type color - %name%', ['%name%' => $parameterValue->getText()]));

        return $this->render(
            'Admin/Content/ParameterValue/edit.html.twig',
            [
                'parameterValue' => $parameterValue,
                'form' => $form->createView(),
            ],
        );
    }
}
