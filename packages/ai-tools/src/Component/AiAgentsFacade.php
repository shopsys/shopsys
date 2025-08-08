<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Component;

use Ramsey\Uuid\Uuid;
use Shopsys\AiToolsBundle\Component\Ai\Application\AiChatSessionFacade;
use Shopsys\AiToolsBundle\Model\Chat\Agent\AgentFacade;
use Shopsys\AiToolsBundle\Model\Chat\Agent\FunctionCalling\AiFunction;
use Shopsys\AiToolsBundle\Model\Chat\ChatDataFactory;
use Shopsys\AiToolsBundle\Model\Chat\ChatFacade;

class AiAgentsFacade
{
    /**
     * @param \Shopsys\AiToolsBundle\Component\Ai\Application\AiChatSessionFacade $aiChatSessionFacade
     * @param \Shopsys\AiToolsBundle\Model\Chat\ChatDataFactory $chatDataFactory
     * @param \Shopsys\AiToolsBundle\Model\Chat\Agent\AgentFacade $agentFacade
     * @param \Shopsys\AiToolsBundle\Model\Chat\ChatFacade $chatFacade
     */
    public function __construct(
        protected readonly AiChatSessionFacade $aiChatSessionFacade,
        protected readonly ChatDataFactory $chatDataFactory,
        protected readonly AgentFacade $agentFacade,
        protected readonly ChatFacade $chatFacade,
    ) {
    }

    /**
     * @param string $text
     * @return string
     */
    #[AiFunction(aiFunctionName: 'translateAgent')]
    public function translate(string $text): string
    {
        $chatData = $this->chatDataFactory->create();
        $chatData->identifier = Uuid::uuid4();
        $chatData->agent = $this->agentFacade->findAgentByInternalKey('translatator');

        $chat = $this->chatFacade->create($chatData);

        return $this->aiChatSessionFacade->ask($chat, $text)->getAnswer();
    }
}
