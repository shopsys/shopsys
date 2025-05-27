<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Model\Chat;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\AiToolsBundle\Model\Chat\Message\ChatMessage;
use Shopsys\AiToolsBundle\Model\Chat\Message\ChatMessageFactory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChatFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\AiToolsBundle\Model\Chat\ChatRepository $chatRepository
     * @param \Shopsys\AiToolsBundle\Model\Chat\ChatFactory $chatFactory
     * @param \Shopsys\AiToolsBundle\Model\Chat\Message\ChatMessageFactory $chatMessageFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ChatRepository $chatRepository,
        protected readonly ChatFactory $chatFactory,
        protected readonly ChatMessageFactory $chatMessageFactory,
    ) {
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\ChatData $chatData
     * @return \Shopsys\AiToolsBundle\Model\Chat\Chat
     */
    public function create(ChatData $chatData): Chat
    {
        $chat = $this->chatFactory->create($chatData);
        $this->em->persist($chat);
        $this->em->flush();

        return $chat;
    }

    /**
     * @param string $identifier
     * @return \Shopsys\AiToolsBundle\Model\Chat\Chat|null
     */
    public function getChatByIdentifier(string $identifier): ?Chat
    {
        $chat = $this->chatRepository->findByIdentifier($identifier);

        if ($chat === null) {
            throw new NotFoundHttpException(sprintf('Chat with identifier %s not found.', $identifier));
        }

        return $chat;
    }

    /**
     * @param int $id
     */
    public function delete(int $id): void
    {
        $chat = $this->chatRepository->findById($id);

        if (!$chat) {
            return;
        }

        $this->em->remove($chat);
        $this->em->flush();
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\Chat $chat
     * @param string $question
     * @return \Shopsys\AiToolsBundle\Model\Chat\Message\ChatMessage
     */
    public function addQuestion(Chat $chat, string $question): ChatMessage
    {
        $chatMessage = $this->chatMessageFactory->create($chat, $question);
        $this->em->persist($chatMessage);
        $this->em->flush();

        $chat->addMessage($chatMessage);
        $this->em->flush();

        return $chatMessage;
    }
}
