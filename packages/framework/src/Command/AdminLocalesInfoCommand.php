<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Override;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorLocalizationFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'shopsys:admin-locales:info',
    description: 'Loads and displays all admin locales',
)]
class AdminLocalesInfoCommand extends Command
{
    public function __construct(
        protected readonly AdministratorLocalizationFacade $administratorLocalizationFacade,
    ) {
        parent::__construct();
    }

    #[Override]
    public function run(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->writeln(implode("\t", $this->administratorLocalizationFacade->getAllowedAdminLocales()));

        return Command::SUCCESS;
    }
}
