<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Form\Admin\Product\Parameter\Value\ParameterValueFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParameterValueController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactory $parameterValueDataFactory
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     */
    public function __construct(
        protected readonly GridFactory $gridFactory,
        protected readonly ParameterRepository $parameterRepository,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly ParameterFacade $parameterFacade,
        protected readonly ParameterValueDataFactory $parameterValueDataFactory,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
    ) {
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/parameter-value/list', name: 'admin_parametervalue_list')]
    public function listAction(): Response
    {
        $domainConfig = $this->adminDomainTabsFacade->getSelectedDomainConfig();

        $queryBuilder = $this->parameterRepository->getQueryBuilderParameterValuesUsedByProductsByLocaleAndType($domainConfig->getLocale(), Parameter::PARAMETER_TYPE_COLOR);
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'pv.id');

        $grid = $this->gridFactory->create('parameterValues', $dataSource);

        $grid->addColumn('text', 'pv.text', t('Parameter value'));
        $grid->addColumn('rgbHex', 'pv.rgbHex', t('RGB Hex'));
        $grid->addEditActionColumn('admin_parametervalue_edit', ['id' => 'pv.id']);
        $grid->setTheme('@ShopsysFramework/Admin/Content/ParameterValue/listGrid.html.twig');

        return $this->render(
            '@ShopsysFramework/Admin/Content/ParameterValue/list.html.twig',
            [
                'gridView' => $grid->createView(),
            ],
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/parameter-value/edit/{id}', name: 'admin_parametervalue_edit', requirements: ['id' => '\d+'])]
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

        $this->breadcrumbOverrider->overrideLastItem(t('Editing parameter value of type color - %name%', ['%name%' => $parameterValue->getText()]));

        return $this->render(
            '@ShopsysFramework/Admin/Content/ParameterValue/edit.html.twig',
            [
                'parameterValue' => $parameterValue,
                'form' => $form->createView(),
            ],
        );
    }
}
