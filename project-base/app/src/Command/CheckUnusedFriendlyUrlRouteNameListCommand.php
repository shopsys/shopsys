<?php

declare(strict_types=1);

namespace App\Command;

use App\Component\Router\FriendlyUrl\FriendlyUrlRepository;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'shopsys:friendly-urls:check-entity-mapping')]
class CheckUnusedFriendlyUrlRouteNameListCommand extends Command
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(private FriendlyUrlFacade $friendlyUrlFacade)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Checks validity of route names mapping to entities');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->comment('Checks friendly URLs route name mapping to entity...');

        $undefinedRouteNamesInMapping = $this->friendlyUrlFacade->getUndefinedRouteNamesInMapping();

        if (count($undefinedRouteNamesInMapping) === 0) {
            $io->success('Friendly URLs mapping is OK.');

            return Command::SUCCESS;
        }

        $io->error(sprintf(
            'Friendly URL mapping is incomplete in "%s". Following route names need to be defined: "%s"',
            FriendlyUrlRepository::class . '::getRouteNameToEntityMap()',
            implode(',', $undefinedRouteNamesInMapping),
        ));

        return Command::FAILURE;
    }
}
