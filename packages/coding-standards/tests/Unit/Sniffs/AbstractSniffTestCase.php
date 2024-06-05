<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Files\LocalFile;
use PHP_CodeSniffer\Runner;
use PHPUnit\Framework\TestCase;

abstract class AbstractSniffTestCase extends TestCase
{
    /**
     * @return string
     */
    abstract protected function getSniffClassName(): string;

    /**
     * @param string $fileToTest
     */
    public function runWrongFilesTest(string $fileToTest): void
    {
        $file = $this->doRunSniff($fileToTest);

        self::assertGreaterThan(0, $file->getErrorCount(), $fileToTest . ' should raise error');
    }

    /**
     * @param string $fileToTest
     */
    public function runCorrectFilesTest(string $fileToTest): void
    {
        $file = $this->doRunSniff($fileToTest);

        self::assertEquals(0, $file->getErrorCount(), $fileToTest . ' should not raise error');
    }

    /**
     * @param string $fileToTest
     * @return \PHP_CodeSniffer\Files\File
     */
    protected function doRunSniff(string $fileToTest): File
    {
        if (defined('PHP_CODESNIFFER_CBF') === false) {
            define('PHP_CODESNIFFER_CBF', false);
        }

        if (defined('PHP_CODESNIFFER_VERBOSITY') === false) {
            define('PHP_CODESNIFFER_VERBOSITY', 0);
        }

        if (defined('PHP_CODESNIFFER_IN_TESTS') === false) {
            define('PHP_CODESNIFFER_IN_TESTS', true);
        }

        $sniffClassName = $this->getSniffClassName();

        $codeSnifferRunner = new Runner();
        $codeSnifferRunner->config = new Config();
        $codeSnifferRunner->init();

        $sniff = new $sniffClassName();

        $codeSnifferRunner->ruleset->sniffs = [$sniffClassName => $sniff];
        $codeSnifferRunner->ruleset->populateTokenListeners();

        $file = new LocalFile($fileToTest, $codeSnifferRunner->ruleset, $codeSnifferRunner->config);
        $file->process();

        return $file;
    }
}
