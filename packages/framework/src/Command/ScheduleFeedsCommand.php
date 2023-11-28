<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Model\Feed\FeedFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'shopsys:feed-schedule')]
class ScheduleFeedsCommand extends Command
{
    private const OPTION_FEED_NAME = 'feed-name';
    private const OPTION_ALL = 'all';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedFacade $feedFacade
     */
    public function __construct(
        protected readonly FeedFacade $feedFacade,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Schedule feeds to be generated in the next cron run.')
            ->addOption(self::OPTION_FEED_NAME, null, InputOption::VALUE_OPTIONAL, 'name of feed to be scheduled')
            ->addOption(self::OPTION_ALL, null, InputOption::VALUE_NONE, 'schedule all feeds');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $optionAll = $input->getOption(self::OPTION_ALL);
        $optionFeedName = $input->getOption(self::OPTION_FEED_NAME);

        $symfonyStyle = new SymfonyStyle($input, $output);

        if ($optionAll === true) {
            $symfonyStyle->info('Scheduling all feeds...');
            $this->feedFacade->scheduleAllFeeds();
        } elseif ($optionFeedName !== null) {
            $symfonyStyle->info('Scheduling feed...');
            $this->feedFacade->scheduleFeedByName($optionFeedName);
        } else {
            $symfonyStyle->error('You have to specify either --all or --feed-name option.');

            return Command::FAILURE;
        }

        $symfonyStyle->success('Done!');

        return Command::SUCCESS;
    }
}
