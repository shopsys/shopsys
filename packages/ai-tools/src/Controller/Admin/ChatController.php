<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Controller\Admin;

use Ramsey\Uuid\Uuid;
use Shopsys\AiToolsBundle\Component\Ai\Application\AiChatSessionFacade;
use Shopsys\AiToolsBundle\Form\Admin\Chat\ChatFormType;
use Shopsys\AiToolsBundle\Model\Chat\Agent\AgentFacade;
use Shopsys\AiToolsBundle\Model\Chat\ChatDataFactory;
use Shopsys\AiToolsBundle\Model\Chat\ChatFacade;
use Shopsys\AiToolsBundle\Model\Chat\ChatRepository;
use Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\AiToolsBundle\Model\Chat\ChatFacade $chatFacade
     * @param \Shopsys\AiToolsBundle\Model\Chat\ChatRepository $chatRepository
     * @param \Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory $confirmDeleteResponseFactory
     * @param \Shopsys\AiToolsBundle\Model\Chat\Agent\AgentFacade $agentFacade
     * @param \Shopsys\AiToolsBundle\Model\Chat\ChatDataFactory $chatDataFactory
     * @param \Shopsys\AiToolsBundle\Component\Ai\Application\AiChatSessionFacade $aiChatSessionFacade
     */
    public function __construct(
        protected readonly GridFactory $gridFactory,
        protected readonly ChatFacade $chatFacade,
        protected readonly ChatRepository $chatRepository,
        protected readonly ConfirmDeleteResponseFactory $confirmDeleteResponseFactory,
        protected readonly AgentFacade $agentFacade,
        protected readonly ChatDataFactory $chatDataFactory,
        protected readonly AiChatSessionFacade $aiChatSessionFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/chat/new/')]
    public function newAction(Request $request): Response
    {
        $questionData = ['question' => ''];
        $form = $this->createForm(ChatFormType::class, $questionData, ['chat' => null]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $question = $form->getData()['question'];
            $agent = $form->getData()['agent'];

            $chatData = $this->chatDataFactory->create();
            $chatData->identifier = Uuid::uuid4()->toString();
            $chatData->agent = $agent;

            $chat = $this->chatFacade->create($chatData);

            $this->aiChatSessionFacade->ask($chat, $question);

            $this
                ->addSuccessFlashTwig(
                    t('Chat with question handled successfully!'),
                );

            return $this->redirectToRoute('shopsys_aitools_admin_chat_edit', ['identifier' => $chat->getIdentifier()]);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAiTools/Admin/Content/Chat/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $identifier
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/chat/edit/{identifier}', requirements: ['identifier' => '\S+'])]
    public function editAction(Request $request, string $identifier): Response
    {
        $chat = $this->chatFacade->getChatByIdentifier($identifier);

        $questionData = ['question' => ''];
        $form = $this->createForm(ChatFormType::class, $questionData, ['chat' => $chat]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $question = $form->getData()['question'];

            $this->aiChatSessionFacade->ask($chat, $question);

            $this
                ->addSuccessFlashTwig(
                    t('Question handled successfully!'),
                );

            return $this->redirectToRoute('shopsys_aitools_admin_chat_edit', ['identifier' => $identifier]);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAiTools/Admin/Content/Chat/edit.html.twig', [
            'form' => $form->createView(),
            'chat' => $chat,
        ]);
    }

    /**
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/chat/delete/{id}', requirements: ['id' => '\d+'])]
    public function deleteAction(int $id): Response
    {
        try {
            $this->chatFacade->delete($id);

            $this->addSuccessFlashTwig(
                t('Chat deleted'),
            );
        } catch (NotFoundHttpException $ex) {
            $this->addErrorFlash(t('Selected chat doesn\'t exist.'));
        }

        return $this->redirectToRoute('shopsys_aitools_admin_chat_list');
    }

    /**
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/chat/delete-confirm/{id}', requirements: ['id' => '\d+'])]
    public function deleteConfirmAction(int $id): Response
    {
        $message = t('Do you really want to remove this chat?');

        return $this->confirmDeleteResponseFactory->createDeleteResponse($message, 'shopsys_aitools_admin_chat_delete', $id);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/chat/list/')]
    public function listAction(): Response
    {
        $grid = $this->getGrid();

        return $this->render('@ShopsysAiTools/Admin/Content/Chat/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    protected function getGrid(): Grid
    {
        $queryBuilder = $this->chatRepository->getAllChatsQueryBuilder();

        $dataSource = new QueryBuilderDataSource($queryBuilder, 'ch.id');

        $gridId = 'chats-grid';
        $grid = $this->gridFactory->create($gridId, $dataSource);
        $grid->setDefaultOrder('id');

        $grid->addColumn('question', 'm.question', t('Question'));
        $grid->addColumn('name', 'a.name', t('Agent name'));
        $grid->addColumn('model', 'am.name', t('Model'));


        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('shopsys_aitools_admin_chat_edit', ['identifier' => 'ch.identifier']);
        $grid->addDeleteActionColumn('shopsys_aitools_admin_chat_deleteconfirm', ['id' => 'ch.id'])
            ->setAjaxConfirm();

        $grid->setTheme('@ShopsysAiTools/Admin/Content/Chat/listGrid.html.twig');

        return $grid;
    }

    /**
     * @param string $identifier
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $agentInternalIdentifier
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/chat/save-message/{identifier}/{agentInternalIdentifier}/', name: 'shopsys_aitools_admin_chat_savemessage', requirements: ['identifier' => '\S+', 'agentInternalIdentifier' => '\S+'])]
    public function saveMessageAction(string $identifier, string $agentInternalIdentifier, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $message = $data['question'] ?? null;

        try {
            $chat = $this->chatFacade->getChatByIdentifier($identifier);
        } catch (NotFoundHttpException) {
            $chatData = $this->chatDataFactory->create();
            $chatData->identifier = $identifier;
            $chatData->agent = $this->agentFacade->findAgentByInternalKey($agentInternalIdentifier);

            $chat = $this->chatFacade->create($chatData);
        }

        if ($message && strlen($message) > 0) {
            $this->aiChatSessionFacade->ask($chat, $message);
        }

        return $this->render('@ShopsysAiTools/Admin/Content/Chat/chatHistory.html.twig', [
            'chat' => $chat,
            'debug' => false,
        ]);
    }

    /**
     * @param string $identifier
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/chat/load/{identifier}/', name: 'shopsys_aitools_admin_chat_load', requirements: ['identifier' => '\S+'])]
    public function chatLoadAction(string $identifier): Response
    {
        try {
            $chat = $this->chatFacade->getChatByIdentifier($identifier);
        } catch (NotFoundHttpException) {
            return $this->render('@ShopsysAiTools/Admin/Content/Chat/chatInformation.html.twig', ['information' => [t('You can ask your question.')]]);
        }

        return $this->render('@ShopsysAiTools/Admin/Content/Chat/chatHistory.html.twig', [
            'chat' => $chat,
            'debug' => false,
        ]);
    }
}
