<?php

declare(strict_types=1);

namespace App;

use Composer\IO\IOInterface;
use Composer\Script\Event;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentFileSetting;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;

class Environment
{
    private static ?EnvironmentFileSetting $environmentFileSetting = null;

    /**
     * @param \Composer\Script\Event $event
     */
    public static function checkEnvironment(Event $event)
    {
        /** @var \Composer\IO\IOInterface $io */
        $io = $event->getIO();

        $environmentFileSetting = self::getEnvironmentFileSetting();
        if (!$environmentFileSetting->isAnyEnvironmentSet()) {
            $environment = $event->isDevMode() ? EnvironmentType::DEVELOPMENT : EnvironmentType::PRODUCTION;
            $environmentFileSetting->createFileForEnvironment($environment);
            $environmentFilePath = $environmentFileSetting->getEnvironmentFilePath($environment);
            $io->write(sprintf('Created a file "%s" to set the application environment!', $environmentFilePath));
        }
        self::printEnvironmentInfo($io);
    }

    /**
     * @return string
     */
    public static function getEnvironment(): string
    {
        return self::getEnvironmentFileSetting()->getEnvironment();
    }

    /**
     * @param \Composer\IO\IOInterface $io
     */
    public static function printEnvironmentInfo(IOInterface $io)
    {
        $io->write("\nEnvironment is <info>" . self::getEnvironment() . "</info>\n");
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Environment\EnvironmentFileSetting
     */
    private static function getEnvironmentFileSetting()
    {
        if (self::$environmentFileSetting === null) {
            self::$environmentFileSetting = new EnvironmentFileSetting(__DIR__ . '/..');
        }
        return self::$environmentFileSetting;
    }
}
