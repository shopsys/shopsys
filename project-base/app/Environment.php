<?php

namespace Shopsys;

use Composer\IO\IOInterface;
use Composer\Script\Event;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentFileSetting;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;

class Environment
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Environment\EnvironmentFileSetting|null
     */
    private static $environmentFileSetting;

    public static function checkEnvironment(Event $event): void
    {
        $io = $event->getIO();
        /* @var $io \Composer\IO\IOInterface */
        $environmentFileSetting = self::getEnvironmentFileSetting();
        if ($io->isInteractive() && !$environmentFileSetting->isAnyEnvironmentSet()) {
            if ($io->askConfirmation('Build in production environment? (Y/n): ', true)) {
                $environmentFileSetting->createFileForEnvironment(EnvironmentType::PRODUCTION);
            } else {
                $environmentFileSetting->createFileForEnvironment(EnvironmentType::DEVELOPMENT);
            }
        }
        self::printEnvironmentInfo($io);
    }
    
    public static function getEnvironment(bool $console): string
    {
        return self::getEnvironmentFileSetting()->getEnvironment($console);
    }

    public static function printEnvironmentInfo(IOInterface $io): void
    {
        $io->write("\nEnvironment is <info>" . self::getEnvironment(false) . "</info>\n");
    }

    private static function getEnvironmentFileSetting(): \Shopsys\FrameworkBundle\Component\Environment\EnvironmentFileSetting
    {
        if (self::$environmentFileSetting === null) {
            self::$environmentFileSetting = new EnvironmentFileSetting(__DIR__ . '/..');
        }
        return self::$environmentFileSetting;
    }
}
