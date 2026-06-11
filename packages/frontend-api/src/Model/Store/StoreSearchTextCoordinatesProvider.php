<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Store;

use Psr\Cache\CacheItemInterface;
use Shopsys\FrameworkBundle\Component\AddressCoordinates\AddressCoordinatesData;
use Shopsys\FrameworkBundle\Component\AddressCoordinates\GoogleAddressCoordinatesFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Contracts\Cache\CacheInterface;

class StoreSearchTextCoordinatesProvider
{
    public function __construct(
        protected readonly GoogleAddressCoordinatesFacade $googleAddressCoordinatesFacade,
        protected readonly Domain $domain,
        protected readonly CacheInterface $storeSearchCoordinatesCache,
    ) {
    }

    /**
     * @return array{latitude: string, longitude: string}|null
     */
    public function getCoordinatesFromSearchText(?string $searchText): ?array
    {
        $normalizedSearchText = $this->normalizeSearchText($searchText);

        if ($normalizedSearchText === null) {
            return null;
        }

        $defaultCountryCode = $this->getDefaultCountryCode();

        return $this->getCachedCoordinatesBySearchText($normalizedSearchText, $defaultCountryCode);
    }

    protected function normalizeSearchText(?string $searchText): ?string
    {
        if ($searchText === null) {
            return null;
        }

        $searchText = trim((string)preg_replace('/\s+/', ' ', $searchText));

        return $searchText !== '' ? $searchText : null;
    }

    /**
     * @return array{latitude: string, longitude: string}|null
     */
    protected function getCachedCoordinatesBySearchText(string $searchText, string $countryCode): ?array
    {
        return $this->storeSearchCoordinatesCache->get(
            $this->getCacheId($searchText, $countryCode),
            function (CacheItemInterface $cacheItem, bool &$save) use ($searchText, $countryCode): ?array {
                $coordinates = $this->formatCoordinatesData(
                    $this->googleAddressCoordinatesFacade->getCoordinatesByUnstructuredAddress(
                        $this->formatUnstructuredAddress($searchText, $countryCode),
                    ),
                );

                if ($coordinates === null) {
                    $save = false;
                }

                return $coordinates;
            },
        );
    }

    /**
     * @return array{latitude: string, longitude: string}|null
     */
    protected function formatCoordinatesData(?AddressCoordinatesData $addressCoordinatesData): ?array
    {
        if ($addressCoordinatesData === null) {
            return null;
        }

        return [
            'latitude' => (string)$addressCoordinatesData->latitude,
            'longitude' => (string)$addressCoordinatesData->longitude,
        ];
    }

    protected function getDefaultCountryCode(): string
    {
        return match ($this->domain->getLocale()) {
            'sk' => 'SK',
            default => 'CZ',
        };
    }

    protected function formatUnstructuredAddress(string $searchText, string $countryCode): string
    {
        return sprintf('%s, %s', $searchText, strtoupper($countryCode));
    }

    protected function getCacheId(string $searchText, string $countryCode): string
    {
        return hash('sha256', sprintf('%s:%s', strtoupper($countryCode), mb_strtolower($searchText)));
    }
}
