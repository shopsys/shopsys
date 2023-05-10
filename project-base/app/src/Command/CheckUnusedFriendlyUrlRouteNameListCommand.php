<?php

declare(strict_types=1);

namespace App\Command;

use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use App\Component\Router\FriendlyUrl\FriendlyUrlRepository;
use Shopsys\FrameworkBundle\Command\CommandResultCodes;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckUnusedFriendlyUrlRouteNameListCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:friendly-urls:check-entity-mapping';

    /**
     * @var \App\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(FriendlyUrlFacade $friendlyUrlFacade)
    {
        parent::__construct();

        $this->friendlyUrlFacade = $friendlyUrlFacade;
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
            return CommandResultCodes::RESULT_OK;
        }

        $io->error(sprintf(
            'Friendly URL mapping is incomplete in "%s". Following route names need to be defined: "%s"',
            FriendlyUrlRepository::class . '::getRouteNameToEntityMap()',
            implode(',', $undefinedRouteNamesInMapping)
        ));

        return CommandResultCodes::RESULT_FAIL;
    }
}
