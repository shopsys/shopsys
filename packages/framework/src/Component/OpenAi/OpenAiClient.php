<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\OpenAi;

use OpenAI\Client;
use OpenAI\Responses\Chat\CreateResponse;
use Shopsys\FrameworkBundle\Model\Chat\Chat;

class OpenAiClient
{
    /**
     * @param \OpenAI\Client $client
     * @param \Shopsys\FrameworkBundle\Component\OpenAi\OpenAiRequestFactory $openAiRequestFactory
     */
    public function __construct(
        protected readonly Client $client,
        protected readonly OpenAiRequestFactory $openAiRequestFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\Chat $chat
     * @return \OpenAI\Responses\Chat\CreateResponse
     */
    public function askSimpleQuestion(Chat $chat): CreateResponse
    {
        return $this->client->chat()->create($this->openAiRequestFactory->getOpenAiSimpleRequest($chat));
    }
}
