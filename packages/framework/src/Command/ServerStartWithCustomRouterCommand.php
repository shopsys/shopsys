<?php

namespace Shopsys\FrameworkBundle\Command;

use Symfony\Bundle\WebServerBundle\Command\ServerStartCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Overrides default "server:start" command because web/index.php is used as front controller.
 *
 * Default behaviour of Symfony is to use router_<environment>.php that requires specific front controller.
 * Front controllers web/app.php and web/app_dev.php were removed because environment is determined by a file
 * in project root.
 */
class ServerStartWithCustomRouterCommand extends ServerStartCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this->getDefinition()->getOption('router')->setDefault('app/router.php');
        $this->setName(self::$defaultName);
    }

    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        if ($input->hasParameterOption(['--env', '-e'])) {
            $io = new SymfonyStyle($input, $output);
            $io->error([
                'Environment passed in --env option is not supported.',
                'Environment can be set by file named DEVELOPMENT, PRODUCTION or TEST in project root.',
            ]);

            return 1;
        }

        return parent::execute($input, $output);
    }
}
