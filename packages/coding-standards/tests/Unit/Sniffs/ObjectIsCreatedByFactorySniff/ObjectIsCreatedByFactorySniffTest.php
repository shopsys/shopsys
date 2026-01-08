<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\ObjectIsCreatedByFactorySniff;

use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\CodingStandards\Sniffs\ObjectIsCreatedByFactorySniff;
use Tests\CodingStandards\Unit\Sniffs\AbstractSniffTestCase;

final class ObjectIsCreatedByFactorySniffTest extends AbstractSniffTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getSniffClassName(): string
    {
        return ObjectIsCreatedByFactorySniff::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function getCorrectFiles(): iterable
    {
        yield [__DIR__ . '/Correct/PostFactory.php'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getWrongFiles(): iterable
    {
        require_once __DIR__ . '/Wrong/PostFactory.php';

        yield [__DIR__ . '/Wrong/SomeController.php'];
    }

    /**
     * @param string $fileToTest
     */
    #[DataProvider('getWrongFiles')]
    public function testWrongFiles(string $fileToTest): void
    {
        $this->runWrongFilesTest($fileToTest);
    }

    /**
     * @param string $fileToTest
     */
    #[DataProvider('getCorrectFiles')]
    public function testCorrectFiles(string $fileToTest): void
    {
        $this->runCorrectFilesTest($fileToTest);
    }
}
