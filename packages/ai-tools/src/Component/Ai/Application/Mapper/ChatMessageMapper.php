<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Component\Ai\Application\Mapper;

use Shopsys\AiToolsBundle\Component\Ai\Client\OpenAi\OpenAiFunctionCallingFactory;
use Shopsys\AiToolsBundle\Component\Ai\Dto\ChatMessage as DtoChatMessage;
use Shopsys\AiToolsBundle\Component\Ai\Dto\ChatRoleEnum;
use Shopsys\AiToolsBundle\Model\Chat\Message\ChatMessage as EntityChatMessage;

class ChatMessageMapper
{
    /**
     * @param \Shopsys\AiToolsBundle\Component\Ai\Client\OpenAi\OpenAiFunctionCallingFactory $openAiFunctionCallingFactory
     */
    public function __construct(
        protected readonly OpenAiFunctionCallingFactory $openAiFunctionCallingFactory,
    ) {
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\Message\ChatMessage $entity
     * @return \Shopsys\AiToolsBundle\Component\Ai\Dto\ChatMessage[]
     */
    public function mapChatToDtoChatMessages(EntityChatMessage $entity): array
    {
        $chat = $entity->getChat();
        $agent = $chat->getAgent();

        $dtoMessages = [];
        $dtoMessages[] = new DtoChatMessage(
            role: ChatRoleEnum::SYSTEM,
            content: $agent->getSetup(),
        );

        foreach ($chat->getMessages() as $messageEntity) {
            foreach ($this->mapChatMessageToDtoChatMessage($messageEntity) as $dto) {
                $dtoMessages[] = $dto;
            }
        }

        return $dtoMessages;
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\Message\ChatMessage $entity
     * @return \Shopsys\AiToolsBundle\Component\Ai\Dto\ChatMessage[]
     */
    protected function mapChatMessageToDtoChatMessage(EntityChatMessage $entity): array
    {
        $dtos = [];

        if ($entity->getType() === EntityChatMessage::TYPE_FUNCTION) {
            $res = $entity->getFunctionCallResult();
            $dtos[] = new DtoChatMessage(
                role: ChatRoleEnum::FUNCTION,
                content: (string)$res['content'],
                name: $res['name'],
            );

            return $dtos;
        }

        if ($entity->getQuestion() !== null && $entity->getQuestion() !== '') {
            $dtos[] = new DtoChatMessage(
                role: ChatRoleEnum::USER,
                content: $entity->getQuestion(),
            );
        }

        if ($entity->getAnswer() !== null && $entity->getAnswer() !== '') {
            $dtos[] = new DtoChatMessage(
                role: ChatRoleEnum::ASSISTANT,
                content: $entity->getAnswer(),
            );
        }

        return $dtos;
    }
}
