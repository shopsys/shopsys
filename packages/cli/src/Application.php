<?php

declare(strict_types=1);

namespace Shopsys\Cli;

use Override;
use Shopsys\Cli\Command\InitCommand;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Command\ListCommand;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class Application extends BaseApplication
{
    public const string NAME = 'Shopsys CLI';

    public const string VERSION = '@cli_version@';

    public function __construct()
    {
        parent::__construct(self::NAME, self::VERSION);

        $container = $this->buildContainer();

        $this->addCommands([
            /** @phpstan-ignore symfonyContainer.serviceNotFound */
            $container->get(InitCommand::class),
        ]);
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    private function buildContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.project_dir', dirname(__DIR__));

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../config'),
        );
        $loader->load('services.yaml');

        $container->compile();

        return $container;
    }

    /**
     * @return \Symfony\Component\Console\Command\Command[]
     */
    #[Override]
    protected function getDefaultCommands(): array
    {
        return [
            new HelpCommand(),
            new ListCommand(),
        ];
    }
}
