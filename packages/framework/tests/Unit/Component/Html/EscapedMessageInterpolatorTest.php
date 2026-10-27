<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Html;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Html\EscapedMessageInterpolator;
use Twig\Markup;

class EscapedMessageInterpolatorTest extends TestCase
{
    private const string XSS_PAYLOAD = '<img src=x onerror="alert(1)">';
    private const string ESCAPED_XSS_PAYLOAD = '&lt;img src=x onerror=&quot;alert(1)&quot;&gt;';

    public function testInterpolateEscapesParametersAndKeepsMessageMarkup(): void
    {
        $escapedMessageInterpolator = new EscapedMessageInterpolator();

        $interpolatedMessage = $escapedMessageInterpolator->interpolate(
            'Warehouse <a href="%url%">%stockName%</a> is not enabled.',
            [
                '%url%' => '/admin/stock/edit/1?a=1&b=2',
                '%stockName%' => self::XSS_PAYLOAD,
            ],
        );

        $this->assertSame(
            'Warehouse <a href="/admin/stock/edit/1?a=1&amp;b=2">' . self::ESCAPED_XSS_PAYLOAD . '</a> is not enabled.',
            $interpolatedMessage,
        );
    }

    public function testInterpolateRendersIntegerAndNullParameters(): void
    {
        $escapedMessageInterpolator = new EscapedMessageInterpolator();

        $interpolatedMessage = $escapedMessageInterpolator->interpolate(
            'Public holidays for year %year% are not set for "%name%".',
            [
                '%year%' => 2026,
                '%name%' => null,
            ],
        );

        $this->assertSame('Public holidays for year 2026 are not set for "".', $interpolatedMessage);
    }

    public function testInterpolateDoesNotReplacePlaceholdersInsideParameterValues(): void
    {
        $escapedMessageInterpolator = new EscapedMessageInterpolator();

        $interpolatedMessage = $escapedMessageInterpolator->interpolate(
            'Remove "%name%" from "%domainName%"?',
            [
                '%name%' => '%domainName%',
                '%domainName%' => 'example.com',
            ],
        );

        $this->assertSame('Remove "%domainName%" from "example.com"?', $interpolatedMessage);
    }

    public function testInterpolateKeepsMarkupParameters(): void
    {
        $escapedMessageInterpolator = new EscapedMessageInterpolator();

        $interpolatedMessage = $escapedMessageInterpolator->interpolate(
            'Customers:<br>%customers%',
            ['%customers%' => new Markup('first@example.com<br>' . self::ESCAPED_XSS_PAYLOAD, 'UTF-8')],
        );

        $this->assertSame('Customers:<br>first@example.com<br>' . self::ESCAPED_XSS_PAYLOAD, $interpolatedMessage);
    }

    public function testCreateHtmlEscapesPlainTextMessageAndParameters(): void
    {
        $escapedMessageInterpolator = new EscapedMessageInterpolator();

        $html = $escapedMessageInterpolator->createHtml(
            'Remove <b>"%name%"</b>?',
            ['%name%' => self::XSS_PAYLOAD],
        );

        $this->assertSame('Remove &lt;b&gt;&quot;' . self::ESCAPED_XSS_PAYLOAD . '&quot;&lt;/b&gt;?', (string)$html);
    }

    public function testCreateHtmlKeepsMarkupMessageAndEscapesParameters(): void
    {
        $escapedMessageInterpolator = new EscapedMessageInterpolator();

        $html = $escapedMessageInterpolator->createHtml(
            new Markup('Remove "%name%"?<br>It is not used anywhere.', 'UTF-8'),
            ['%name%' => self::XSS_PAYLOAD],
        );

        $this->assertSame('Remove "' . self::ESCAPED_XSS_PAYLOAD . '"?<br>It is not used anywhere.', (string)$html);
    }

    public function testEscapeEscapesHtmlSpecialCharacters(): void
    {
        $escapedMessageInterpolator = new EscapedMessageInterpolator();

        $this->assertSame(self::ESCAPED_XSS_PAYLOAD, $escapedMessageInterpolator->escape(self::XSS_PAYLOAD));
    }
}
