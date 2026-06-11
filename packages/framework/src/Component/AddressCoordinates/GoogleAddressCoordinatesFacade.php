<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\AddressCoordinates;

use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\AddressCoordinates\Exception\GoogleAddressCoordinatesException;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GoogleAddressCoordinatesFacade
{
    protected const string GEOCODE_ADDRESS_URL = 'https://geocode.googleapis.com/v4/geocode/address';

    public function __construct(
        protected readonly HttpClientInterface $httpClient,
        protected readonly string $googleMapApiKey,
        protected readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Shopsys\FrameworkBundle\Component\AddressCoordinates\Exception\GoogleAddressCoordinatesException
     */
    public function getCoordinatesByStructuredAddress(
        string $street,
        string $city,
        string $countryCode,
        string $postcode,
    ): ?AddressCoordinatesData {
        if (!$this->isGoogleApiAvailable()) {
            return null;
        }

        $query = $this->createAddressQuery($street, $city, $countryCode, $postcode);

        if ($query === null) {
            return null;
        }

        return $this->getCoordinatesByQuery($query);
    }

    public function getCoordinatesByUnstructuredAddress(string $address): ?AddressCoordinatesData
    {
        if (!$this->isGoogleApiAvailable() || $address === '') {
            return null;
        }

        return $this->getCoordinatesByQuery([
            'key' => $this->googleMapApiKey,
            'addressQuery' => $address,
        ]);
    }

    /**
     * @param array<string, string> $query
     */
    protected function getCoordinatesByQuery(array $query): ?AddressCoordinatesData
    {
        try {
            $response = $this->httpClient->request('GET', static::GEOCODE_ADDRESS_URL, [
                'query' => $query,
                'headers' => [
                    'X-Goog-FieldMask' => 'results.location',
                ],
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                $this->logger->error('Google Geocode API returned unsuccessful status code.', [
                    'statusCode' => $statusCode,
                ]);

                throw new GoogleAddressCoordinatesException(sprintf(
                    'Google Geocode API returned unsuccessful status code "%d".',
                    $statusCode,
                ));
            }

            $data = $response->toArray(false);
        } catch (TransportExceptionInterface | DecodingExceptionInterface $exception) {
            $this->logger->error('Getting address coordinates from Google Geocode API failed.', [
                'exception' => $exception,
            ]);

            throw new GoogleAddressCoordinatesException(
                'Getting address coordinates from Google Geocode API failed.',
                $exception,
            );
        }

        $latitude = $data['results'][0]['location']['latitude'] ?? null;
        $longitude = $data['results'][0]['location']['longitude'] ?? null;

        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            return null;
        }

        return new AddressCoordinatesData((float)$latitude, (float)$longitude);
    }

    /**
     * @return array<string, string>|null
     */
    protected function createAddressQuery(
        string $street,
        string $city,
        string $countryCode,
        string $postcode,
    ): ?array {
        $query = [
            'key' => $this->googleMapApiKey,
        ];

        if ($street !== '') {
            $query['address.addressLines'] = $street;
        }

        if ($city !== '') {
            $query['address.locality'] = $city;
        }

        if ($countryCode !== '') {
            $query['address.regionCode'] = strtoupper($countryCode);
        }

        if ($postcode !== '') {
            $query['address.postalCode'] = $postcode;
        }

        return count($query) > 1 ? $query : null;
    }

    public function isGoogleApiAvailable(): bool
    {
        return $this->googleMapApiKey !== '';
    }
}
