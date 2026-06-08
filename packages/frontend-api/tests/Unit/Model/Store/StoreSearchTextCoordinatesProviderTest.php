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
            'searchText' => 'Ost',
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
    ): StoreSearchTextCoordinatesProvider {
        $domainStub = $this->createStub(Domain::class);
        $domainStub->method('getLocale')->willReturn($locale);

        return new StoreSearchTextCoordinatesProvider(
            $googleAddressCoordinatesFacadeMock,
            $domainStub,
        );
    }

    private function createGoogleAddressCoordinatesFacadeMock(): GoogleAddressCoordinatesFacade|MockObject
    {
        return $this->createMock(GoogleAddressCoordinatesFacade::class);
    }
}
