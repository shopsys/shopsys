<?php

namespace Shopsys\FrameworkBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class ConfigureDomainsUrlsCommand extends Command
{
    /**
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnlySniff.ReferenceViaFullyQualifiedName
     */
    protected static $defaultName = 'shopsys:domains-urls:configure';

    private Filesystem $localFilesystem;

    private string $configFilepath;

    /**
     * @param \Symfony\Component\Filesystem\Filesystem $localFilesystem
     * @param string $configFilepath
     */
    public function __construct(
        Filesystem $localFilesystem,
        string $configFilepath
    ) {
        $this->localFilesystem = $localFilesystem;
        $this->configFilepath = $configFilepath;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Copies domain URL configuration from .dist template if it\'s not set yet');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->localFilesystem->exists($this->configFilepath)) {
            $output->writeln('<fg=green>URLs for domains were already configured.</fg=green>');
        } else {
            $output->writeln('URLs for domains were not configured yet.');
            $this->localFilesystem->copy($this->configFilepath . '.dist', $this->configFilepath);
            $output->writeln(
                sprintf('<fg=green>Copied the default configuration into "%s".</fg=green>', $this->configFilepath)
            );
        }

        return Command::SUCCESS;
    }
}
