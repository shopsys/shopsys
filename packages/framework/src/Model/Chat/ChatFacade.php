<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Chat;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\OpenAi\OpenAiFacade;
use Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage;
use Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessageFactory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChatFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Chat\ChatRepository $chatRepository
     * @param \Shopsys\FrameworkBundle\Model\Chat\ChatFactory $chatFactory
     * @param \Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessageFactory $chatMessageFactory
     * @param \Shopsys\FrameworkBundle\Component\OpenAi\OpenAiFacade $openAiFacade
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ChatRepository $chatRepository,
        protected readonly ChatFactory $chatFactory,
        protected readonly ChatMessageFactory $chatMessageFactory,
        protected readonly OpenAiFacade $openAiFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\ChatData $chatData
     * @return \Shopsys\FrameworkBundle\Model\Chat\Chat
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
     * @return \Shopsys\FrameworkBundle\Model\Chat\Chat|null
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
     * @param \Shopsys\FrameworkBundle\Model\Chat\Chat $chat
     * @param string $question
     * @return \Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage
     */
    private function addQuestion(Chat $chat, string $question): ChatMessage
    {
        $chatMessage = $this->chatMessageFactory->create($chat, $question);
        $this->em->persist($chatMessage);
        $this->em->flush();

        $chat->addMessage($chatMessage);
        $this->em->flush();

        return $chatMessage;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\Chat $chat
     * @param string $question
     * @return \Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage
     */
    public function handleQuestion(Chat $chat, string $question): ChatMessage
    {
        $chatMessage = $this->addQuestion($chat, $question);

        //some factory for resolving AI service by Agent setup(model)
        $this->openAiFacade->handleQuestion($chatMessage);

        return $chatMessage;
    }
}
