<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Router;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Router\UrlNormalizer;
use Tests\FrameworkBundle\Test\DomainConfigHelper;

class UrlNormalizerTest extends TestCase
{
    /**
     * @return iterable<string, array{url: string|null, expectedUrl: string|null}>
     */
    public static function normalizeUrlProvider(): iterable
    {
        yield 'null URL remains null' => [
            'url' => null,
            'expectedUrl' => null,
        ];

        yield 'relative URL with leading slash remains unchanged' => [
            'url' => '/url-address',
            'expectedUrl' => '/url-address',
        ];

        yield 'slug without leading slash is converted to relative URL' => [
            'url' => 'url-address',
            'expectedUrl' => '/url-address',
        ];

        yield 'absolute URL on same domain is converted to relative URL' => [
            'url' => DomainConfigHelper::DEFAULT_EXAMPLE_COM_BASE_URL . '/url-address',
            'expectedUrl' => '/url-address',
        ];

        yield 'same domain homepage URL is converted to root relative URL' => [
            'url' => DomainConfigHelper::DEFAULT_EXAMPLE_COM_BASE_URL,
            'expectedUrl' => '/',
        ];

        yield 'absolute external HTTPS URL remains unchanged' => [
            'url' => 'https://external.example.com/url-address',
            'expectedUrl' => 'https://external.example.com/url-address',
        ];

        yield 'absolute external HTTP URL remains unchanged' => [
            'url' => 'http://external.example.com/url-address',
            'expectedUrl' => 'http://external.example.com/url-address',
        ];

        yield 'external URL without protocol is converted to HTTPS URL' => [
            'url' => 'www.external.example.com/url-address',
            'expectedUrl' => 'https://www.external.example.com/url-address',
        ];
    }

    #[DataProvider('normalizeUrlProvider')]
    public function testNormalizeUrl(?string $url, ?string $expectedUrl): void
    {
        $domainConfig = DomainConfigHelper::getDomainConfig();

        $normalizedUrl = UrlNormalizer::normalizeUrl($url, $domainConfig);

        $this->assertSame($expectedUrl, $normalizedUrl);
    }
}
