<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Form\Admin\Agent\AgentFormType;
use Shopsys\FrameworkBundle\Model\Chat\Agent\AgentDataFactory;
use Shopsys\FrameworkBundle\Model\Chat\Agent\AgentFacade;
use Shopsys\FrameworkBundle\Model\Chat\Agent\AgentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class AgentController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\Chat\Agent\AgentRepository $agentRepository
     * @param \Shopsys\FrameworkBundle\Model\Chat\Agent\AgentDataFactory $agentDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Chat\Agent\AgentFacade $agentFacade
     * @param \Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory $confirmDeleteResponseFactory
     */
    public function __construct(
        protected readonly GridFactory $gridFactory,
        protected readonly AgentRepository $agentRepository,
        protected readonly AgentDataFactory $agentDataFactory,
        protected readonly AgentFacade $agentFacade,
        protected readonly ConfirmDeleteResponseFactory $confirmDeleteResponseFactory,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/agent/new/')]
    public function newAction(Request $request): Response
    {
        $agentData = $this->agentDataFactory->create();

        $form = $this->createForm(AgentFormType::class, $agentData, [
            'agent' => null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $agent = $this->agentFacade->create($agentData);

            $this
                ->addSuccessFlashTwig(
                    t('Agent <strong><a href="{{ url }}">{{ name }}</a></strong> created'),
                    [
                        'name' => $agent->getName(),
                        'url' => $this->generateUrl('admin_agent_edit', ['id' => $agent->getId()]),
                    ],
                );

            return $this->redirectToRoute('admin_agent_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/Agent/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/agent/edit/{id}', requirements: ['id' => '\d+'])]
    public function editAction(Request $request, int $id): Response
    {
        $agent = $this->agentFacade->getById($id);
        $agentData = $this->agentDataFactory->createFromAgent($agent);

        $form = $this->createForm(AgentFormType::class, $agentData, [
            'agent' => $agent,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $agent = $this->agentFacade->edit($id, $agentData);

            $this
                ->addSuccessFlashTwig(
                    t('Agent <strong><a href="{{ url }}">{{ name }}</a></strong> edited'),
                    [
                        'name' => $agent->getName(),
                        'url' => $this->generateUrl('admin_agent_edit', ['id' => $agent->getId()]),
                    ],
                );

            return $this->redirectToRoute('admin_agent_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/Agent/edit.html.twig', [
            'form' => $form->createView(),
            'agent' => $agent,
        ]);
    }

    /**
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/agent/delete/{id}', requirements: ['id' => '\d+'])]
    public function deleteAction(int $id): Response
    {
        try {
            $fullName = $this->agentFacade->getById($id)->getName();

            $this->agentFacade->delete($id);

            $this->addSuccessFlashTwig(
                t('Agent <strong>{{ name }}</strong> deleted'),
                [
                    'name' => $fullName,
                ],
            );
        } catch (NotFoundHttpException $ex) {
            $this->addErrorFlash(t('Selected agent doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_agent_list');
    }

    /**
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/agent/delete-confirm/{id}', requirements: ['id' => '\d+'])]
    public function deleteConfirmAction(int $id): Response
    {
        $agent = $this->agentFacade->getById($id);

        if ($this->agentFacade->isAgentUsed($agent)) {
            $message = t(
                'Because agent "%name%"  is used with other chats also, you have to choose a new agent which will replace '
                . 'the existing one. Which agent you want to set to these chats? ',
                ['%name%' => $agent->getName()],
            );

            return $this->confirmDeleteResponseFactory->createSetNewAndDeleteResponse(
                $message,
                'admin_agent_delete',
                $id,
                $this->agentFacade->getAllExceptId($id),
            );
        }

        $message = t('Do you really want to remove this agent?');

        return $this->confirmDeleteResponseFactory->createDeleteResponse($message, 'admin_agent_delete', $id);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/agent/list/')]
    public function listAction(): Response
    {
        $grid = $this->getGrid();

        return $this->render('@ShopsysFramework/Admin/Content/Agent/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    protected function getGrid(): Grid
    {
        $queryBuilder = $this->agentRepository->getAllAgentsQueryBuilder();

        $dataSource = new QueryBuilderDataSource($queryBuilder, 'a.id');

        $gridId = 'agents-grid';
        $grid = $this->gridFactory->create($gridId, $dataSource);
        $grid->setDefaultOrder('id');

        $grid->addColumn('name', 'a.name', t('Name'));
        $grid->addColumn('model', 'a.model', t('Model'));
        $grid->addColumn('enabled', 'a.enabled', t('Enabled'));

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_agent_edit', ['id' => 'a.id']);
        $grid->addDeleteActionColumn('admin_agent_deleteconfirm', ['id' => 'a.id'])
            ->setAjaxConfirm();

        $grid->setTheme('@ShopsysFramework/Admin/Content/Agent/listGrid.html.twig');

        return $grid;
    }
}
