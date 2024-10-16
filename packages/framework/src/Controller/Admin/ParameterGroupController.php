<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Form\Admin\Product\Parameter\ParameterGroupFormType;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Exception\ParameterGroupNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParameterGroupController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupFacade $parameterGroupFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupDataFactory $parameterGroupDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly ParameterGroupFacade $parameterGroupFacade,
        protected readonly GridFactory $gridFactory,
        protected readonly ParameterGroupDataFactory $parameterGroupDataFactory,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/product/parameter-group/list/')]
    public function listAction(): Response
    {
        $grid = $this->getGrid();

        return $this->render('@ShopsysFramework/Admin/Content/ParameterGroup/list.html.twig', [
            'grid' => $grid->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[Route(path: '/product/parameter-group/save-ordering/', condition: 'request.isXmlHttpRequest()')]
    public function saveOrderingAction(Request $request): JsonResponse
    {
        $this->parameterGroupFacade->saveOrdering($request->get('rowIds'));

        $responseData = ['success' => true];

        return new JsonResponse($responseData);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/product/parameter-group/new/')]
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

        return $this->render('@ShopsysFramework/Admin/Content/ParameterGroup/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/product/parameter-group/edit/{id}', requirements: ['id' => '\d+'])]
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

        return $this->render('@ShopsysFramework/Admin/Content/ParameterGroup/edit.html.twig', [
            'form' => $form->createView(),
            'parameterGroup' => $parameterGroup,
        ]);
    }

    /**
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    #[Route(path: '/product/parameter-group/delete/{id}', requirements: ['id' => '\d+'])]
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

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    protected function getGrid(): Grid
    {
        $queryBuilder = $this->parameterGroupFacade->getOrderedParameterGroupsQueryBuilder($this->domain->getLocale());

        $dataSource = new QueryBuilderDataSource($queryBuilder, 'pg.id');

        $grid = $this->gridFactory->create('parameterGroupsList', $dataSource);

        $grid->addColumn('name', 'pgt.name', t('Name'));
        $grid->setDefaultOrder('pg.position');

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_parametergroup_edit', ['id' => 'pg.id']);
        $grid->addDeleteActionColumn('admin_parametergroup_delete', ['id' => 'pg.id'])
            ->setConfirmMessage(t('Do you really want to remove this parameter groups? By deleting this parameter group you will '
                . 'unset all groups by associated parameters. This step is irreversible!'));

        $grid->enableDragAndDrop(ParameterGroup::class);

        $grid->setTheme('@ShopsysFramework/Admin/Content/ParameterGroup/listGrid.html.twig');

        return $grid;
    }
}
