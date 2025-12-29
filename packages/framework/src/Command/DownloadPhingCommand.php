<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Component\Phing\PhingDownloader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'shopsys:phing:download')]
class DownloadPhingCommand extends Command
{
    private const DEFAULT_PHING_VERSION = '3.0.1';

    /**
     * @param string $vendorDir
     */
    public function __construct(private readonly string $vendorDir)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Downloads Phing PHAR file')
            ->addArgument(
                'version',
                InputArgument::OPTIONAL,
                'Phing version to download',
                self::DEFAULT_PHING_VERSION,
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $version = $input->getArgument('version');

        $projectDir = dirname($this->vendorDir);
        $downloader = new PhingDownloader($projectDir);

        try {
            $downloader->download($version);
            $symfonyStyle->success(sprintf('Phing %s downloaded successfully.', $version));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $symfonyStyle->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
