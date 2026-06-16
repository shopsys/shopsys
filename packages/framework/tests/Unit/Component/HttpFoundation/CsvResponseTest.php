<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\HttpFoundation;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\HttpFoundation\CsvResponse;
use Symfony\Component\Serializer\Encoder\CsvEncoder;

final class CsvResponseTest extends TestCase
{
    public function testCsvResponseEscapesFormulasByDefault(): void
    {
        $response = new CsvResponse(
            [
                ['email' => '=1+1@evil.com'],
                ['email' => '+foo@evil.com'],
                ['email' => '-foo@evil.com'],
                ['email' => '@foo@example.com'],
                ['email' => 'normal@example.com'],
            ],
            'emails.csv',
        );

        $this->assertSame(
            "email\n'=1+1@evil.com\n'+foo@evil.com\n'-foo@evil.com\n'@foo@example.com\nnormal@example.com\n",
            $response->getContent(),
        );
    }

    public function testCsvResponseSupportsHeaderlessSemicolonSeparatedExport(): void
    {
        $response = new CsvResponse(
            [
                ['=1+1@evil.com', '2026-06-15'],
            ],
            'emails.csv',
            null,
            [
                CsvEncoder::DELIMITER_KEY => ';',
                CsvEncoder::NO_HEADERS_KEY => true,
            ],
        );

        $this->assertSame("'=1+1@evil.com;2026-06-15\n", $response->getContent());
    }

    public function testCsvResponseStreamsIterableData(): void
    {
        $response = new CsvResponse(
            $this->createCsvRows(),
            'emails.csv',
            null,
            [
                CsvEncoder::DELIMITER_KEY => ';',
                CsvEncoder::NO_HEADERS_KEY => true,
            ],
        );

        $this->assertSame(
            "normal@example.com\n'=1+1@evil.com\n",
            $response->getContent(),
        );
    }

    public function testCsvResponseStreamsIterableDataWithHeadersFromFirstRow(): void
    {
        $response = new CsvResponse(
            $this->createAssociativeCsvRows(),
            'emails.csv',
        );

        $this->assertSame(
            "email\nnormal@example.com\n'=1+1@evil.com\n",
            $response->getContent(),
        );
    }

    /**
     * @return iterable<int, array{0: string}>
     */
    private function createCsvRows(): iterable
    {
        yield ['normal@example.com'];

        yield ['=1+1@evil.com'];
    }

    /**
     * @return iterable<int, array{email: string}>
     */
    private function createAssociativeCsvRows(): iterable
    {
        yield ['email' => 'normal@example.com'];

        yield ['email' => '=1+1@evil.com'];
    }
}
