<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @deprecated Command is no longer necessary as proper public dir can be set in elfinder:install with --docroot option
 */
class ElFinderPostInstallCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:elfinder:post-install';

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
        $symfonyStyleIo = new SymfonyStyle($input, $output);

        $symfonyStyleIo->warning([
            'The shopsys:elfinder:post-install command is deprecated and will be removed in the next major. ',
            'Set public dir via --docroot option of elfinder:install command.',
        ]);

        $publicDir = $this->parameterBag->get('kernel.project_dir') . '/public/bundles/fmelfinder';
        $webDir = $this->parameterBag->get('kernel.project_dir') . '/web/bundles/fmelfinder';

        if ($this->filesystem->exists($publicDir . '/css')) {
            $symfonyStyleIo->text('Moving elFinder from ' . $publicDir . ' to ' . $webDir . '');

            $this->filesystem->mirror($publicDir, $webDir);

            $symfonyStyleIo->text('Removing elFinder public directory');
            $this->filesystem->remove($publicDir);
        }

        if ($this->filesystem->exists($webDir . '/css')) {
            $symfonyStyleIo->success('elFinder assets successfully installed');
            return CommandResultCodes::RESULT_OK;
        }
        $symfonyStyleIo->error('elFinder post-install failed');
        return CommandResultCodes::RESULT_FAIL;
    }
}
