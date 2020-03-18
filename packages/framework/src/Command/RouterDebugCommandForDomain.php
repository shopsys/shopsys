<?php

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Component\Console\DomainChoiceHandler;
use Symfony\Bundle\FrameworkBundle\Command\RouterDebugCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\Debug\FileLinkFormatter;
use Symfony\Component\Routing\RouterInterface;

class RouterDebugCommandForDomain extends RouterDebugCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'debug:router';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Console\DomainChoiceHandler
     */
    private $domainChoiceHelper;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Console\DomainChoiceHandler $domainChoiceHelper
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Symfony\Component\HttpKernel\Debug\FileLinkFormatter|null $fileLinkFormatter
     */
    public function __construct(DomainChoiceHandler $domainChoiceHelper, RouterInterface $router, ?FileLinkFormatter $fileLinkFormatter = null)
    {
        $this->domainChoiceHelper = $domainChoiceHelper;

        parent::__construct($router, $fileLinkFormatter);
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
