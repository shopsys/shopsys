<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Component\Ai\Client;

use Shopsys\AiToolsBundle\Component\Ai\Exception\UnsupportedModelException;
use Shopsys\AiToolsBundle\Model\Chat\Agent\Agent;
use Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModel;
use Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelApiSourceEnum;
use Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelRepository;

final class AIClientFactory
{
    /**
     * @param iterable<\Shopsys\AiToolsBundle\Component\Ai\Client\AIClientInterface> $clients  (service-locator podle resource) např. ['openai' => OpenAIClient, ...]
     * @param \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelRepository $aiModelRepository
     */
    public function __construct(
        private readonly iterable $clients,
        private readonly AiModelRepository $aiModelRepository,
    ) {
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\Agent\Agent $agent
     * @return \Shopsys\AiToolsBundle\Component\Ai\Client\AIClientInterface
     */
    public function getClientByAgent(Agent $agent): AIClientInterface
    {
        $aiModel = $agent->getAiModel();

        return $this->getClientByAiModel($aiModel);
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModel $aiModel
     * @return \Shopsys\AiToolsBundle\Component\Ai\Client\AIClientInterface
     */
    public function getClientByAiModel(AiModel $aiModel): AIClientInterface
    {
        $apiSource = $aiModel->getApiSource();

        return $this->getClientByApiSource($apiSource);
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelApiSourceEnum $aiModelApiSourceEnum
     * @return \Shopsys\AiToolsBundle\Component\Ai\Client\AIClientInterface
     */
    public function getClientByApiSource(AiModelApiSourceEnum $aiModelApiSourceEnum): AIClientInterface
    {
        foreach ($this->clients as $key => $client) {
            if ($key === $aiModelApiSourceEnum->value) {
                return $client;
            }
        }

        throw new UnsupportedModelException(
            sprintf('Model API provider "%s" has connected any client.', $aiModelApiSourceEnum->value),
        );
    }
}
