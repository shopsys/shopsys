<?php

declare(strict_types=1);

namespace App\Command;

use App\DataFixtures\Demo\AgentDataFixture;
use Exception;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\OpenAi\OpenAiClient;
use Shopsys\FrameworkBundle\Model\Chat\Agent\AgentFacade;
use Shopsys\FrameworkBundle\Model\Chat\Agent\FunctionCalling\DynamicFunctionRunner;
use Shopsys\FrameworkBundle\Model\Chat\ChatDataFactory;
use Shopsys\FrameworkBundle\Model\Chat\ChatFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'shopsys:openai-testing',
    description: 'Import performance data to test db. Demo and base data fixtures must be imported first',
)]
class OpenAiTestingCommand extends Command
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\OpenAi\OpenAiClient $openAiClient
     * @param \Shopsys\FrameworkBundle\Model\Chat\ChatFacade $chatFacade
     * @param \Shopsys\FrameworkBundle\Model\Chat\ChatDataFactory $chatDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Chat\Agent\AgentFacade $agentFacade
     * @param \Shopsys\FrameworkBundle\Model\Chat\Agent\FunctionCalling\DynamicFunctionRunner $functionCallingFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly OpenAIClient $openAiClient,
        protected readonly ChatFacade $chatFacade,
        protected readonly ChatDataFactory $chatDataFactory,
        protected readonly AgentFacade $agentFacade,
        protected readonly DynamicFunctionRunner $functionCallingFacade,
        protected readonly Domain $domain,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this->addArgument('question');
        $this->domain->switchDomainById(Domain::FIRST_DOMAIN_ID);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //        d($locale = $this->functionCallingFacade->call('getCurrentLocale'));
        //        d($this->functionCallingFacade->call('getProductNameByCatnum', [
        //            'catnum' => '9177759',
        //            'locale' => $locale,
        //        ]));


        $question = $input->getArgument('question');

        $output->writeln('<fg=green>Question: </fg=green>' . $question);

        $userIdentifier = '654600d9-72fb-4047-be2c-7473f42e1e6a';

        try {
            $chat = $this->chatFacade->getChatByIdentifier($userIdentifier);
        } catch (Exception $exception) {
            $chatData = $this->chatDataFactory->create();
            $chatData->identifier = $userIdentifier;
            $chatData->agent = $this->agentFacade->findAgentByInternalKey(AgentDataFixture::AGENT_ASTROLOG_KEY);

            $chat = $this->chatFacade->create($chatData);
        }

        $this->chatFacade->handleQuestion($chat, $question);

        $output->writeln('<fg=green>Chat: </fg=green>' . $chat->getWholeCommunication());

        return Command::SUCCESS;
    }
}
