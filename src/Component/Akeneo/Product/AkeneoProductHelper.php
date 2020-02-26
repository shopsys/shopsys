<?php

declare(strict_types=1);

namespace App\Component\Akeneo\Product;

use App\Component\Akeneo\AkeneoHelper;
use Shopsys\FrameworkBundle\Component\Money\Money;

class AkeneoProductHelper
{
    /**
     * @param array|null $arrayData
     * @return string|null
     */
    public static function mapDataString(?array $arrayData): ?string
    {
        if ($arrayData === null) {
            return $arrayData;
        }

        $mappedData = current($arrayData);
        if (is_array($mappedData)) {
            return (string)$mappedData['data'];
        }

        return null;
    }

    /**
     * @param array $productData
     * @param array|null $akeneoData
     * @return array
     */
    public static function mapLocalizedDataString(array $productData, ?array $akeneoData): array
    {
        foreach ($productData as $key => $value) {
            $productData[$key] = null;
        }

        if ($akeneoData === null) {
            return $productData;
        }

        foreach ($akeneoData as $data) {
            $locale = AkeneoHelper::findEshopLocaleByAkeneoLocale($data['locale']);
            if ($locale) {
                $productData[$locale] = $data['data'];
            }
        }

        return $productData;
    }

    /**
     * @param array $productData
     * @param array|null $akeneoData
     * @return array
     */
    public static function mapDomainDataString(array $productData, ?array $akeneoData): array
    {
        foreach ($productData as $key => $value) {
            $productData[$key] = null;
        }

        if ($akeneoData === null) {
            return $productData;
        }

        foreach ($akeneoData as $data) {
            $domainId = AkeneoHelper::findEshopDomainIdByAkeneoLocale($data['locale']);
            if ($domainId) {
                $productData[$domainId] = $data['data'];
            }
        }

        return $productData;
    }

    /**
     * @param array $productData
     * @param array|null $akeneoData
     * @return \Shopsys\FrameworkBundle\Component\Money\Money[]
     */
    public static function mapDomainDataPrices(array $productData, ?array $akeneoData): array
    {
        foreach ($productData as $key => $value) {
            $productData[$key] = null;
        }

        if ($akeneoData === null) {
            return $productData;
        }

        foreach ($akeneoData as $akeneoPricesData) {
            foreach ($akeneoPricesData['data'] as $akeneoPriceData) {
                $domainId = AkeneoHelper::findEshopDomainIdByCurrencyCode($akeneoPriceData['currency']);
                if ($domainId) {
                    $productData[$domainId] = $akeneoPriceData['amount'] ? Money::create($akeneoPriceData['amount']) : null;
                }
            }
        }

        return $productData;
    }

    /**
     * @param array $productData
     * @param array|null $akeneoData
     * @return array
     */
    public static function mapDomainDataArray(array $productData, ?array $akeneoData): array
    {
        foreach ($productData as $key => $value) {
            $productData[$key] = null;
        }

        if ($akeneoData === null) {
            return $productData;
        }

        foreach ($akeneoData as $locale => $data) {
            $domainId = AkeneoHelper::findEshopDomainIdByAkeneoLocale($locale);
            if ($domainId) {
                $productData[$domainId] = $data;
            }
        }

        return $productData;
    }
}
