<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Override;
use Shopsys\FrameworkBundle\Model\Feed\FeedFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'shopsys:feed-schedule',
    description: 'Schedule feeds to be generated in the next cron run',
)]
class ScheduleFeedsCommand extends Command
{
    protected const OPTION_FEED_NAME = 'feed-name';
    protected const OPTION_ALL = 'all';

    public function __construct(
        protected readonly FeedFacade $feedFacade,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function configure(): void
    {
        $this
            ->addOption(static::OPTION_FEED_NAME, null, InputOption::VALUE_OPTIONAL, 'name of feed to be scheduled')
            ->addOption(static::OPTION_ALL, null, InputOption::VALUE_NONE, 'schedule all feeds');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $optionAll = $input->getOption(static::OPTION_ALL);
        $optionFeedName = $input->getOption(static::OPTION_FEED_NAME);

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
