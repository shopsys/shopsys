<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\HttpFoundation;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\HttpFoundation\CspHeaderSanitizer;

class CspHeaderSanitizerTest extends TestCase
{
    private const string EXPECTED_SANITIZED_VALUE = "frame-ancestors 'self'; default-src 'self' https: 'unsafe-inline' data:";

    #[DataProvider('sanitizeDataProvider')]
    public function testSanitize(string $input): void
    {
        $cspHeaderSanitizer = new CspHeaderSanitizer();

        $actual = $cspHeaderSanitizer->sanitize($input);

        $this->assertSame(self::EXPECTED_SANITIZED_VALUE, $actual);
    }

    /**
     * @return iterable<string, string[]>
     */
    public static function sanitizeDataProvider(): iterable
    {
        yield 'single line unchanged' => [
            self::EXPECTED_SANITIZED_VALUE,
        ];

        yield 'line breaks normalized to single spaces' => [
            "frame-ancestors 'self';\ndefault-src 'self' https: 'unsafe-inline' data:",
        ];

        yield 'mixed whitespace around line breaks collapsed' => [
            "  frame-ancestors 'self'; \r\n\t default-src 'self' https: 'unsafe-inline' data:  ",
        ];

        yield 'multiple consecutive lines collapse to one space' => [
            "frame-ancestors 'self';\n\n\n default-src 'self' https: 'unsafe-inline' data:",
        ];

        yield 'spaces around newline are collapsed' => [
            "frame-ancestors 'self';  \n  default-src 'self' https: 'unsafe-inline' data:",
        ];

        yield 'tabs around windows line break are collapsed' => [
            "frame-ancestors 'self';\t\r\n\tdefault-src 'self' https: 'unsafe-inline' data:",
        ];

        yield 'leading and trailing empty lines are trimmed' => [
            "\n\n frame-ancestors 'self';\ndefault-src 'self' https: 'unsafe-inline' data:\n\n",
        ];
    }
}
