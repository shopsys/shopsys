<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Console;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Shopsys\FrameworkBundle\Component\Console\ConsoleExitCodeSubscriber;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Taken from https://github.com/VasekPurchart/Console-Errors-Bundle/blob/master/tests/Console/ConsoleExitCodeListenerTest.php
 * because so far, the bundle does not support PHP 8, @see https://github.com/VasekPurchart/Console-Errors-Bundle/issues/11
 */
class ConsoleExitCodeSubscriberTest extends TestCase
{
    public function testLogError(): void
    {
        $commandName = 'hello:world';
        $exitCode = 123;

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('log')
            ->with(LogLevel::ERROR, $this->logicalAnd(
                $this->stringContains($commandName),
                $this->stringContains((string)$exitCode),
            ));

        $command = new Command($commandName);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $event = new ConsoleTerminateEvent($command, $input, $output, $exitCode);

        $consoleExitCodeSubscriber = new ConsoleExitCodeSubscriber($logger);
        $consoleExitCodeSubscriber->onConsoleTerminate($event);
    }

    public function testLogErrorExitCodeMax255(): void
    {
        $commandName = 'hello:world';
        $exitCode = 999;

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('log')
            ->with(LogLevel::ERROR, $this->logicalAnd(
                $this->stringContains($commandName),
                $this->stringContains('255'),
            ));

        $command = new Command($commandName);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $event = new ConsoleTerminateEvent($command, $input, $output, $exitCode);

        $consoleExitCodeSubscriber = new ConsoleExitCodeSubscriber($logger);
        $consoleExitCodeSubscriber->onConsoleTerminate($event);
    }

    public function testZeroExitCodeDoesNotLog(): void
    {
        $commandName = 'hello:world';
        $exitCode = 0;

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->never())
            ->method('log');

        $command = new Command($commandName);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $event = new ConsoleTerminateEvent($command, $input, $output, $exitCode);

        $consoleExitCodeSubscriber = new ConsoleExitCodeSubscriber($logger);
        $consoleExitCodeSubscriber->onConsoleTerminate($event);
    }
}
