<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\Store;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\AddressCoordinates\AddressCoordinatesData;
use Shopsys\FrameworkBundle\Component\AddressCoordinates\GoogleAddressCoordinatesFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Store\StoreSearchTextCoordinatesProvider;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class StoreSearchTextCoordinatesProviderTest extends TestCase
{
    public function testGetsCoordinatesByPostcode(): void
    {
        $googleAddressCoordinatesFacadeMock = $this->createGoogleAddressCoordinatesFacadeMock();
        $googleAddressCoordinatesFacadeMock->expects($this->once())
            ->method('getCoordinatesByAddress')
            ->with('', '', 'CZ', '70800')
            ->willReturn(new AddressCoordinatesData(49.8209, 18.2625));
        $provider = $this->createProvider($googleAddressCoordinatesFacadeMock, 'cs');

        $coordinates = $provider->getCoordinatesFromSearchText('708 00');

        $this->assertSame([
            'latitude' => '49.8209',
            'longitude' => '18.2625',
        ], $coordinates);
    }

    public function testGetsCoordinatesByPostcodeWithoutSpace(): void
    {
        $googleAddressCoordinatesFacadeMock = $this->createGoogleAddressCoordinatesFacadeMock();
        $googleAddressCoordinatesFacadeMock->expects($this->once())
            ->method('getCoordinatesByAddress')
            ->with('', '', 'CZ', '70800')
            ->willReturn(new AddressCoordinatesData(49.8209, 18.2625));
        $provider = $this->createProvider($googleAddressCoordinatesFacadeMock, 'cs');

        $coordinates = $provider->getCoordinatesFromSearchText('70800');

        $this->assertSame([
            'latitude' => '49.8209',
            'longitude' => '18.2625',
        ], $coordinates);
    }

    public function testGetsCoordinatesByCityOutsideStoredCities(): void
    {
        $googleAddressCoordinatesFacadeMock = $this->createGoogleAddressCoordinatesFacadeMock();
        $googleAddressCoordinatesFacadeMock->expects($this->once())
            ->method('getCoordinatesByAddress')
            ->with('', 'Havířov', 'CZ', '')
            ->willReturn(new AddressCoordinatesData(49.7798, 18.4369));
        $provider = $this->createProvider($googleAddressCoordinatesFacadeMock, 'cs');

        $coordinates = $provider->getCoordinatesFromSearchText('Havířov');

        $this->assertSame([
            'latitude' => '49.7798',
            'longitude' => '18.4369',
        ], $coordinates);
    }

    public function testUsesSlovakCountryCodeOnSlovakDomain(): void
    {
        $googleAddressCoordinatesFacadeMock = $this->createGoogleAddressCoordinatesFacadeMock();
        $googleAddressCoordinatesFacadeMock->expects($this->once())
            ->method('getCoordinatesByAddress')
            ->with('', 'Žilina', 'SK', '')
            ->willReturn(new AddressCoordinatesData(49.2231, 18.7394));
        $provider = $this->createProvider($googleAddressCoordinatesFacadeMock, 'sk');

        $coordinates = $provider->getCoordinatesFromSearchText('Žilina');

        $this->assertSame([
            'latitude' => '49.2231',
            'longitude' => '18.7394',
        ], $coordinates);
    }

    public function testReturnsNullWhenGoogleDoesNotReturnCoordinates(): void
    {
        $googleAddressCoordinatesFacadeMock = $this->createGoogleAddressCoordinatesFacadeMock();
        $googleAddressCoordinatesFacadeMock->expects($this->once())
            ->method('getCoordinatesByAddress')
            ->with('', 'Neexistující', 'CZ', '')
            ->willReturn(null);
        $provider = $this->createProvider($googleAddressCoordinatesFacadeMock, 'cs');

        $coordinates = $provider->getCoordinatesFromSearchText('Neexistující');

        $this->assertNull($coordinates);
    }

    public function testDoesNotUseCachedNullCoordinatesBySearchText(): void
    {
        $googleAddressCoordinatesFacadeMock = $this->createGoogleAddressCoordinatesFacadeMock();
        $googleAddressCoordinatesFacadeMock->expects($this->exactly(2))
            ->method('getCoordinatesByAddress')
            ->with('', 'Neexistující', 'CZ', '')
            ->willReturn(null);
        $provider = $this->createProvider($googleAddressCoordinatesFacadeMock, 'cs');

        $firstCoordinates = $provider->getCoordinatesFromSearchText('Neexistující');
        $secondCoordinates = $provider->getCoordinatesFromSearchText('Neexistující');

        $this->assertNull($firstCoordinates);
        $this->assertNull($secondCoordinates);
    }

    public function testUsesCachedCoordinatesBySearchText(): void
    {
        $googleAddressCoordinatesFacadeMock = $this->createGoogleAddressCoordinatesFacadeMock();
        $googleAddressCoordinatesFacadeMock->expects($this->once())
            ->method('getCoordinatesByAddress')
            ->with('', 'Havířov', 'CZ', '')
            ->willReturn(new AddressCoordinatesData(49.7798, 18.4369));
        $provider = $this->createProvider($googleAddressCoordinatesFacadeMock, 'cs');

        $firstCoordinates = $provider->getCoordinatesFromSearchText('Havířov');
        $secondCoordinates = $provider->getCoordinatesFromSearchText('Havířov');

        $this->assertSame($firstCoordinates, $secondCoordinates);
        $this->assertSame([
            'latitude' => '49.7798',
            'longitude' => '18.4369',
        ], $secondCoordinates);
    }

    public function testUsesDifferentCacheKeyForDifferentCountry(): void
    {
        $googleAddressCoordinatesFacadeMock = $this->createGoogleAddressCoordinatesFacadeMock();
        $googleAddressCoordinatesFacadeMock->expects($this->exactly(2))
            ->method('getCoordinatesByAddress')
            ->willReturnCallback(static function (
                string $street,
                string $city,
                string $countryCode,
                string $postcode,
            ): AddressCoordinatesData {
                return match ($countryCode) {
                    'CZ' => new AddressCoordinatesData(50.0755, 14.4378),
                    'SK' => new AddressCoordinatesData(48.1486, 17.1077),
                    default => new AddressCoordinatesData(0, 0),
                };
            });
        $cache = new ArrayAdapter();
        $czechProvider = $this->createProvider($googleAddressCoordinatesFacadeMock, 'cs', $cache);
        $slovakProvider = $this->createProvider($googleAddressCoordinatesFacadeMock, 'sk', $cache);

        $czechCoordinates = $czechProvider->getCoordinatesFromSearchText('Praha');
        $slovakCoordinates = $slovakProvider->getCoordinatesFromSearchText('Praha');

        $this->assertSame([
            'latitude' => '50.0755',
            'longitude' => '14.4378',
        ], $czechCoordinates);
        $this->assertSame([
            'latitude' => '48.1486',
            'longitude' => '17.1077',
        ], $slovakCoordinates);
    }

    #[DataProvider('getInvalidSearchTextDataProvider')]
    public function testDoesNotCallGoogleForInvalidSearchText(?string $searchText): void
    {
        $googleAddressCoordinatesFacadeMock = $this->createGoogleAddressCoordinatesFacadeMock();
        $googleAddressCoordinatesFacadeMock->expects($this->never())
            ->method('getCoordinatesByAddress');
        $provider = $this->createProvider($googleAddressCoordinatesFacadeMock, 'cs');

        $coordinates = $provider->getCoordinatesFromSearchText($searchText);

        $this->assertNull($coordinates);
    }

    /**
     * @return iterable<string, array{searchText: string|null}>
     */
    public static function getInvalidSearchTextDataProvider(): iterable
    {
        yield 'null search text' => [
            'searchText' => null,
        ];

        yield 'empty search text' => [
            'searchText' => '',
        ];

        yield 'short city fragment' => [
            'searchText' => 'O',
        ];

        yield 'numeric text that is not postcode' => [
            'searchText' => '1234',
        ];

        yield 'city with number' => [
            'searchText' => 'Praha 4',
        ];

        yield 'unsupported special characters' => [
            'searchText' => 'Praha!',
        ];
    }

    private function createProvider(
        GoogleAddressCoordinatesFacade|MockObject $googleAddressCoordinatesFacadeMock,
        string $locale,
        ?ArrayAdapter $cache = null,
    ): StoreSearchTextCoordinatesProvider {
        $domainStub = $this->createStub(Domain::class);
        $domainStub->method('getLocale')->willReturn($locale);

        return new StoreSearchTextCoordinatesProvider(
            $googleAddressCoordinatesFacadeMock,
            $domainStub,
            $cache ?? new ArrayAdapter(),
        );
    }

    private function createGoogleAddressCoordinatesFacadeMock(): GoogleAddressCoordinatesFacade|MockObject
    {
        return $this->createMock(GoogleAddressCoordinatesFacade::class);
    }
}
