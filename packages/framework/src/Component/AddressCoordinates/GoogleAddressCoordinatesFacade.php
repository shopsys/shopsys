<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\AddressCoordinates;

use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GoogleAddressCoordinatesFacade
{
    protected const string GEOCODE_ADDRESS_URL = 'https://geocode.googleapis.com/v4/geocode/address';

    public function __construct(
        protected readonly HttpClientInterface $httpClient,
        protected readonly string $googleMapApiKey,
    ) {
    }

    public function getCoordinatesByAddress(
        string $street,
        string $city,
        string $countryCode,
        string $postcode,
    ): ?AddressCoordinatesData {
        if ($this->googleMapApiKey === '') {
            return null;
        }

        try {
            $response = $this->httpClient->request('GET', static::GEOCODE_ADDRESS_URL, [
                'query' => [
                    'key' => $this->googleMapApiKey,
                    'address.addressLines' => $street,
                    'address.locality' => $city,
                    'address.regionCode' => strtoupper($countryCode),
                    'address.postalCode' => $postcode,
                ],
                'headers' => [
                    'X-Goog-FieldMask' => 'results.location',
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $data = $response->toArray(false);
        } catch (TransportExceptionInterface | DecodingExceptionInterface) {
            return null;
        }

        $latitude = $data['results'][0]['location']['latitude'] ?? null;
        $longitude = $data['results'][0]['location']['longitude'] ?? null;

        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            return null;
        }

        return new AddressCoordinatesData((float)$latitude, (float)$longitude);
    }
}
