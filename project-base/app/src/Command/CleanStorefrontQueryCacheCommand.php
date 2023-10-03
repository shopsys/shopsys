<?php

declare(strict_types=1);

namespace App\Command;

use App\Component\Redis\CleanStorefrontCacheFacade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CleanStorefrontQueryCacheCommand extends Command
{
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    protected static $defaultName = 'shopsys:redis:clean-storefront-cache';

    /**
     * @param \App\Component\Redis\CleanStorefrontCacheFacade $cleanStorefrontCacheFacade
     */
    public function __construct(
        private readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Cleans up storefront caches');

        $this->addOption('queries', null, InputOption::VALUE_NONE, 'Clean graphql query cache');
        $this->addOption('translations', null, InputOption::VALUE_NONE, 'Clean translations cache');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $shouldCleanQueries = $input->getOption('queries');
        $shouldCleanTranslations = $input->getOption('translations');

        $symfonyStyle = new SymfonyStyle($input, $output);

        if (!$shouldCleanQueries && !$shouldCleanTranslations) {
            $symfonyStyle->error('You have to specify at least one of the options: --queries, --translations');
        }

        if ($shouldCleanQueries) {
            $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache();
            $symfonyStyle->success('Storefront graphql query cache cleaned');
        }

        if ($shouldCleanTranslations) {
            $this->cleanStorefrontCacheFacade->cleanStorefrontTranslationCache();
            $symfonyStyle->success('Storefront translations cache cleaned');
        }

        return Command::SUCCESS;
    }
}
