<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Phing;

use PharIo\Version\InvalidVersionException;
use PharIo\Version\Version;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PhingDownloader
{
    public const string OPTION_VERSION = 'phing-version';
    protected const string VERSION_URL = 'https://github.com/phingofficial/phing/releases/download/%s/phing-%s.phar';
    protected const string SEMVER_REGEX = '/\b\d+\.\d+\.\d+(-[0-9A-Za-z-]+(\.[0-9A-Za-z-]+)*)?(\+[0-9A-Za-z-]+(\.[0-9A-Za-z-]+)*)?\b/';

    /**
     * @param string $vendorDir
     */
    public function __construct(protected readonly string $vendorDir)
    {
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $version = $input->getOption(self::OPTION_VERSION);
        $currentPhingVersion = $this->getCurrentPhingVersion();

        if ($version !== null) {
            try {
                new Version($version);
            } catch (InvalidVersionException) {
                $io->error('Invalid version format.');

                return Command::FAILURE;
            }

            if ($currentPhingVersion !== null && $currentPhingVersion->getVersionString() === $version) {
                $io->success('Phing is already installed in the specified version.');

                return Command::SUCCESS;
            }

            $downloadUrl = sprintf(self::VERSION_URL, $version, $version);
        } else {
            $phingLatestVersion = $this->getLatestPhingVersion();

            if ($currentPhingVersion !== null && $phingLatestVersion !== null && $currentPhingVersion->getVersionString() === $phingLatestVersion->getVersionString()) {
                $io->success('Latest version of Phing is already installed.');

                return Command::SUCCESS;
            }

            $downloadUrl = sprintf(self::VERSION_URL, $phingLatestVersion->getVersionString(), $phingLatestVersion->getVersionString());
        }

        $phar = @file_get_contents($downloadUrl);

        if ($phar === false) {
            $io->error('Phing PHAR could not be downloaded. Make sure you have provided correct version and your internet connection is working correctly.');

            return Command::FAILURE;
        }

        file_put_contents($this->vendorDir . '/../phing.phar', $phar);

        $io->success('Phing PHAR has been downloaded successfully.');

        return Command::SUCCESS;
    }

    /**
     * @return \PharIo\Version\Version|null
     */
    protected function getCurrentPhingVersion(): ?Version
    {
        $phingVersionString = shell_exec('php phing -v');

        return $this->getVersionFromString($phingVersionString);
    }

    /**
     * @return \PharIo\Version\Version|null
     */
    protected function getLatestPhingVersion(): ?Version
    {
        $phingLatestVersionString = shell_exec('composer show phing/phing -l -a | grep latest');

        return $this->getVersionFromString($phingLatestVersionString);
    }

    /**
     * @param string $string
     * @return \PharIo\Version\Version|null
     */
    protected function getVersionFromString(string $string): ?Version
    {
        if (preg_match(self::SEMVER_REGEX, $string, $matches) === 1) {
            return new Version($matches[0]);
        }

        return null;
    }
}
