<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Component\Console\DomainChoiceHandler;
use Symfony\Bundle\FrameworkBundle\Command\RouterMatchCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

class RouterMatchCommandForDomain extends Command
{
    /**
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnlySniff.ReferenceViaFullyQualifiedName
     */
    protected static $defaultName = 'router:match';

    /**
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnlySniff.ReferenceViaFullyQualifiedName
     */
    protected static $defaultDescription = 'Help debug routes by simulating a path info match';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Console\DomainChoiceHandler $domainChoiceHelper
     * @param \Symfony\Bundle\FrameworkBundle\Command\RouterMatchCommand $routerMatchCommand
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
     */
    public function __construct(
        private readonly DomainChoiceHandler $domainChoiceHelper,
        private readonly RouterMatchCommand $routerMatchCommand,
        private readonly KernelInterface $kernel,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition([
                new InputArgument('path_info', InputArgument::REQUIRED, 'A path info'),
                new InputOption('method', null, InputOption::VALUE_REQUIRED, 'Set the HTTP method'),
                new InputOption('scheme', null, InputOption::VALUE_REQUIRED, 'Set the URI scheme (usually http or https)'),
                new InputOption('host', null, InputOption::VALUE_REQUIRED, 'Set the URI host'),
            ])
            ->setDescription(self::$defaultDescription)
            ->setHelp(
                <<<'EOF'
The <info>%command.name%</info> shows which routes match a given request and which don't and for what reason:

  <info>php %command.full_name% /foo</info>

or

  <info>php %command.full_name% /foo --method POST --scheme https --host symfony.com --verbose</info>

EOF,
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->domainChoiceHelper->chooseDomainAndSwitch($io);

        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $this->routerMatchCommand->setApplication($application);

        return $this->routerMatchCommand->run($input, $output);
    }
}
