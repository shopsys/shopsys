<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Component\Console\DomainChoiceHandler;
use Symfony\Bundle\FrameworkBundle\Command\RouterMatchCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\RouterInterface;

class RouterMatchCommandForDomain extends RouterMatchCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'router:match';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Console\DomainChoiceHandler
     */
    private $domainChoiceHelper;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Console\DomainChoiceHandler $domainChoiceHelper
     * @param \Symfony\Component\Routing\RouterInterface $router
     */
    public function __construct(DomainChoiceHandler $domainChoiceHelper, RouterInterface $router)
    {
        $this->domainChoiceHelper = $domainChoiceHelper;

        parent::__construct($router);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->domainChoiceHelper->chooseDomainAndSwitch($io);

        return parent::execute($input, $output);
    }
}
