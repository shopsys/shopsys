<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Component\Plugin\PluginDataFixtureFacade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LoadPluginDataFixturesCommand extends Command
{
    /**
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected static $defaultName = 'shopsys:plugin-data-fixtures:load';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Plugin\PluginDataFixtureFacade $pluginDataFixtureFacade
     */
    public function __construct(private readonly PluginDataFixtureFacade $pluginDataFixtureFacade)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Loads data fixtures of all registered plugins');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->pluginDataFixtureFacade->loadAll();

        return Command::SUCCESS;
    }
}
