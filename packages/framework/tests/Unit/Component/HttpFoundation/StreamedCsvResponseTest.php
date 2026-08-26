<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\HttpFoundation;

use Generator;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\HttpFoundation\StreamedCsvResponse;

class StreamedCsvResponseTest extends TestCase
{
    public function testValuesEvaluatedAsFormulasAreEscaped(): void
    {
        $response = new StreamedCsvResponse(
            $this->createDataIterator(),
            'emails.csv',
            ';',
            false,
        );

        $this->assertSame(
            "'=1+1@evil.com;\"2026-08-14 12:00:00\"\n" .
            "john.doe@example.com;\"2026-08-14 12:00:00\"\n",
            $this->getStreamedContent($response),
        );
    }

    public function testHeaderRowIsStreamedOnlyOnce(): void
    {
        $response = new StreamedCsvResponse($this->createDataIterator(), 'emails.csv');

        $this->assertSame(
            "email,createdAt\n" .
            "'=1+1@evil.com,\"2026-08-14 12:00:00\"\n" .
            "john.doe@example.com,\"2026-08-14 12:00:00\"\n",
            $this->getStreamedContent($response),
        );
    }

    public function testColumnsOfTheFirstRowDefineTheColumnsOfTheWholeExport(): void
    {
        $data = [
            [
                'email' => 'john.doe@example.com',
                'createdAt' => '2026-08-14',
            ],
            [
                'createdAt' => '2026-08-15',
                'email' => 'jane.doe@example.com',
            ],
            [
                'email' => 'no.date@example.com',
            ],
        ];

        $response = new StreamedCsvResponse($data, 'emails.csv');

        $this->assertSame(
            "email,createdAt\n" .
            "john.doe@example.com,2026-08-14\n" .
            "jane.doe@example.com,2026-08-15\n" .
            "no.date@example.com,\n",
            $this->getStreamedContent($response),
        );
    }

    public function testResponseHeaders(): void
    {
        $response = new StreamedCsvResponse($this->createDataIterator(), 'emails.csv');

        $this->assertSame('text/csv; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertSame('attachment; filename="emails.csv"', $response->headers->get('Content-Disposition'));
    }

    /**
     * @return \Generator<array{email: string, createdAt: string}>
     */
    private function createDataIterator(): Generator
    {
        yield [
            'email' => '=1+1@evil.com',
            'createdAt' => '2026-08-14 12:00:00',
        ];

        yield [
            'email' => 'john.doe@example.com',
            'createdAt' => '2026-08-14 12:00:00',
        ];
    }

    private function getStreamedContent(StreamedCsvResponse $response): string
    {
        ob_start();
        $response->sendContent();

        return ob_get_clean();
    }
}
