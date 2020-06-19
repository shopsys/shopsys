<?php

declare(strict_types=1);

namespace App\Command;

use Shopsys\FrameworkBundle\Command\CommandResultCodes;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

class ElFinderPostInstallCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:elfinder:postinstall';

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * @var \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface
     */
    private $parameterBag;

    /**
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameterBag
     */
    public function __construct(Filesystem $filesystem, ParameterBagInterface $parameterBag)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $publicDir = $this->parameterBag->get('kernel.project_dir') . '/public/bundles/fmelfinder';
        $webDir = $this->parameterBag->get('kernel.project_dir') . '/web/bundles/fmelfinder';

        if (!$this->filesystem->exists($webDir . '/css') && $this->filesystem->exists($publicDir . '/css')) {
            $output->writeln('<fg=green>move elFinder from ' . $publicDir . ' to ' . $webDir . ' </fg=green>');

            $this->filesystem->mirror($publicDir, $webDir);

            $output->writeln('<fg=green>remove elFinder public directory </fg=green>');
            $this->filesystem->remove($publicDir);
        }

        if ($this->filesystem->exists($webDir . '/css')) {
            $output->writeln('<fg=green>elFinder assets successfully POST installed</fg=green>');
            return CommandResultCodes::RESULT_OK;
        } else {
            $output->writeln('<fg=red>elFinder POST install FAILED</fg=red>');
            return CommandResultCodes::RESULT_FAIL;
        }
    }
}
