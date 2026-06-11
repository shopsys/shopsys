<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\AddressCoordinates;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Shopsys\FrameworkBundle\Component\AddressCoordinates\GoogleAddressCoordinatesFacade;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class GoogleAddressCoordinatesFacadeTest extends TestCase
{
    public function testGetsCoordinatesByUnstructuredAddress(): void
    {
        $httpClient = new MockHttpClient(function (string $method, string $url, array $options): MockResponse {
            $this->assertSame('GET', $method);
            $this->assertSame('https://geocode.googleapis.com/v4/geocode/address', strtok($url, '?'));
            $this->assertSame(
                'key=google-api-key&addressQuery=Křižíkova 148/34, Praha 8',
                rawurldecode((string)parse_url($url, PHP_URL_QUERY)),
            );
            $this->assertContains('X-Goog-FieldMask: results.location', $options['headers']);

            return $this->createCoordinatesResponse(50.0921, 14.4456);
        });
        $facade = $this->createGoogleAddressCoordinatesFacade($httpClient);

        $coordinates = $facade->getCoordinatesByUnstructuredAddress('Křižíkova 148/34, Praha 8');

        $this->assertNotNull($coordinates);
        $this->assertSame(50.0921, $coordinates->latitude);
        $this->assertSame(14.4456, $coordinates->longitude);
    }

    public function testGetsCoordinatesByStructuredAddress(): void
    {
        $httpClient = new MockHttpClient(function (string $method, string $url, array $options): MockResponse {
            $this->assertSame('GET', $method);
            $this->assertSame('https://geocode.googleapis.com/v4/geocode/address', strtok($url, '?'));
            $this->assertSame(
                'key=google-api-key&address.addressLines=Křižíkova 148/34'
                    . '&address.locality=Praha 8&address.regionCode=CZ&address.postalCode=18600',
                rawurldecode((string)parse_url($url, PHP_URL_QUERY)),
            );
            $this->assertContains('X-Goog-FieldMask: results.location', $options['headers']);

            return $this->createCoordinatesResponse(50.0921, 14.4456);
        });
        $facade = $this->createGoogleAddressCoordinatesFacade($httpClient);

        $coordinates = $facade->getCoordinatesByStructuredAddress('Křižíkova 148/34', 'Praha 8', 'cz', '18600');

        $this->assertNotNull($coordinates);
        $this->assertSame(50.0921, $coordinates->latitude);
        $this->assertSame(14.4456, $coordinates->longitude);
    }

    public function testReturnsNullForEmptyUnstructuredAddressWithoutCallingGoogle(): void
    {
        $httpClient = new MockHttpClient(function (): never {
            $this->fail('Google Geocode API should not be called for an empty unstructured address.');
        });
        $facade = $this->createGoogleAddressCoordinatesFacade($httpClient);

        $coordinates = $facade->getCoordinatesByUnstructuredAddress('');

        $this->assertNull($coordinates);
    }

    private function createGoogleAddressCoordinatesFacade(
        HttpClientInterface $httpClient,
    ): GoogleAddressCoordinatesFacade {
        return new GoogleAddressCoordinatesFacade($httpClient, 'google-api-key', new NullLogger());
    }

    private function createCoordinatesResponse(float $latitude, float $longitude): MockResponse
    {
        return new MockResponse(json_encode([
            'results' => [
                [
                    'location' => [
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR));
    }
}
