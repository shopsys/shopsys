<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Store;

use Shopsys\FrameworkBundle\Component\AddressCoordinates\AddressCoordinatesData;
use Shopsys\FrameworkBundle\Component\AddressCoordinates\GoogleAddressCoordinatesFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class StoreSearchTextCoordinatesProvider
{
    protected const int MIN_CITY_SEARCH_TEXT_LENGTH_FOR_GOOGLE_REQUEST = 4;

    public function __construct(
        protected readonly GoogleAddressCoordinatesFacade $googleAddressCoordinatesFacade,
        protected readonly Domain $domain,
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

        $postcode = $this->getPostcode($normalizedSearchText);

        if ($postcode !== null) {
            return $this->formatCoordinatesData(
                $this->googleAddressCoordinatesFacade->getCoordinatesByAddress(
                    '',
                    '',
                    $this->getDefaultCountryCode(),
                    $postcode,
                ),
            );
        }

        if (!$this->isCitySearchTextUsable($normalizedSearchText)) {
            return null;
        }

        return $this->formatCoordinatesData(
            $this->googleAddressCoordinatesFacade->getCoordinatesByAddress(
                '',
                $normalizedSearchText,
                $this->getDefaultCountryCode(),
                '',
            ),
        );
    }

    protected function normalizeSearchText(?string $searchText): ?string
    {
        if ($searchText === null) {
            return null;
        }

        $searchText = trim((string)preg_replace('/\s+/', ' ', $searchText));

        return $searchText !== '' ? $searchText : null;
    }

    protected function getPostcode(string $searchText): ?string
    {
        if (preg_match('/^\d{3}\s?\d{2}$/', $searchText) !== 1) {
            return null;
        }

        return str_replace(' ', '', $searchText);
    }

    protected function isCitySearchTextUsable(string $searchText): bool
    {
        if (preg_match('/\d/', $searchText) === 1) {
            return false;
        }

        if (preg_match('/[[:alpha:]]/u', $searchText) !== 1) {
            return false;
        }

        if (preg_match('/^[\p{L}\s.\'-]+$/u', $searchText) !== 1) {
            return false;
        }

        return mb_strlen(str_replace(' ', '', $searchText)) >= static::MIN_CITY_SEARCH_TEXT_LENGTH_FOR_GOOGLE_REQUEST;
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
}
