<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanCreate;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Product\Parameter\ParameterGroupFormType;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Exception\ParameterGroupNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_PARAMETER_GROUP)]
class ParameterGroupController extends AdminBaseController
{
    public function __construct(
        protected readonly ParameterGroupFacade $parameterGroupFacade,
        protected readonly GridFactory $gridFactory,
        protected readonly ParameterGroupDataFactory $parameterGroupDataFactory,
        protected readonly QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
        protected readonly Localization $localization,
    ) {
    }

    #[Route(path: '/product/parameter-group/list/')]
    #[CanView]
    public function listAction(): Response
    {
        $grid = $this->getGrid();

        return $this->render('@ShopsysAdministration/content/parameterGroup/list.html.twig', [
            'grid' => $grid->createView(),
        ]);
    }

    #[Route(path: '/product/parameter-group/new/')]
    #[CanCreate]
    public function newAction(Request $request): Response
    {
        $parameterGroupData = $this->parameterGroupDataFactory->create();

        $form = $this->createForm(ParameterGroupFormType::class, $parameterGroupData, [
            'parameterGroup' => null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $parameterGroup = $this->parameterGroupFacade->create($parameterGroupData);

            $this->addSuccessFlashTwig(
                t('Parameter <strong><a href="{{ url }}">{{ name }}</a></strong> created'),
                [
                    'name' => $parameterGroup->getName(),
                    'url' => $this->generateUrl('admin_parametergroup_edit', ['id' => $parameterGroup->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_parametergroup_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAdministration/content/parameterGroup/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/product/parameter-group/edit/{id}', requirements: ['id' => '\d+'])]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function editAction(Request $request, int $id): Response
    {
        $parameterGroup = $this->parameterGroupFacade->getById($id);
        $parameterGroupData = $this->parameterGroupDataFactory->createFromParameterGroup($parameterGroup);

        $form = $this->createForm(ParameterGroupFormType::class, $parameterGroupData, [
            'parameterGroup' => $parameterGroup,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $parameterGroup = $this->parameterGroupFacade->edit($id, $parameterGroupData);

            $this->addSuccessFlashTwig(
                t('Parameter group <strong><a href="{{ url }}">{{ name }}</a></strong> edited'),
                [
                    'name' => $parameterGroup->getName(),
                    'url' => $this->generateUrl('admin_parametergroup_edit', ['id' => $parameterGroup->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_parametergroup_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAdministration/content/parameterGroup/edit.html.twig', [
            'form' => $form->createView(),
            'parameterGroup' => $parameterGroup,
        ]);
    }

    #[Route(path: '/product/parameter-group/delete/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAction(int $id): RedirectResponse
    {
        try {
            $fullName = $this->parameterGroupFacade->getById($id)->getName();
            $this->parameterGroupFacade->deleteById($id);

            $this->addSuccessFlashTwig(
                t('Parameter <strong>{{ name }}</strong> deleted'),
                [
                    'name' => $fullName,
                ],
            );
        } catch (ParameterGroupNotFoundException $ex) {
            $this->addErrorFlash(t('Selected parameter doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_parametergroup_list');
    }

    protected function getGrid(): Grid
    {
        $queryBuilder = $this->parameterGroupFacade->getOrderedParameterGroupsQueryBuilder($this->localization->getCurrentLocaleForTranslatableEntities());

        $dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 'pg.id');

        $grid = $this->gridFactory->create('parameterGroupsList', $dataSource, AdminRoleConstant::ROLE_PARAMETER_GROUP);

        $grid->addColumn('name', 'pgt.name', t('Name'));
        $grid->setDefaultOrder('pg.position');

        $grid->addEditActionColumn('admin_parametergroup_edit', ['id' => 'pg.id']);
        $grid->addDeleteActionColumn('admin_parametergroup_delete', ['id' => 'pg.id'])
            ->setConfirmMessage(t('Do you really want to remove this parameter groups? By deleting this parameter group you will '
                . 'unset all groups by associated parameters. This step is irreversible!'));

        $grid->enableDragAndDrop(ParameterGroup::class);

        $grid->setTheme('@ShopsysAdministration/content/parameterGroup/listGrid.html.twig');

        return $grid;
    }
}
