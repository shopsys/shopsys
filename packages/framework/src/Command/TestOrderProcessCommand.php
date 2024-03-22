<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'shopsys:order:test')]
class TestOrderProcessCommand extends Command
{
    public function __construct(
        private readonly CartFacade $cartFacade,
        private readonly OrderProcessor $orderProcessor,
        private readonly Domain $domain,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Test order process');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cart = $this->cartFacade->findCartByCartIdentifier('eab206b9-df92-4af4-a67b-e41184056afe');
        $domainConfig = $this->domain->getDomainConfigById(1);

        $result = $this->orderProcessor->process($cart, $domainConfig);

        d($result);

        return Command::SUCCESS;
    }
}
