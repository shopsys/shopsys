<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Component\Plugin\PluginDataFixtureFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'shopsys:plugin-data-fixtures:load',
    description: 'Loads data fixtures of all registered plugins',
)]
class LoadPluginDataFixturesCommand extends Command
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Plugin\PluginDataFixtureFacade $pluginDataFixtureFacade
     */
    public function __construct(private readonly PluginDataFixtureFacade $pluginDataFixtureFacade)
    {
        parent::__construct();
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
