<?php

declare(strict_types=1);

namespace App\Component\Akeneo\Product;

use App\Component\Akeneo\AkeneoHelper;

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
     * @return array
     */
    public static function mapDomainDataPriceString(array $productData, ?array $akeneoData): array
    {
        foreach ($productData as $key => $value) {
            $productData[$key] = null;
        }

        if ($akeneoData === null) {
            return $productData;
        }

        foreach ($akeneoData as $data) {
            foreach ($data['data'] as $currentPrice) {
                $domainId = AkeneoHelper::findEshopDomainIdByCurrency($currentPrice['currency']);
                if ($domainId) {
                    $productData[$domainId] = $currentPrice['amount'];
                }
            }
        }

        return $productData;
    }
}
