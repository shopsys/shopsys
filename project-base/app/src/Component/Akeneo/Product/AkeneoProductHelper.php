<?php

declare(strict_types=1);

namespace App\Component\Akeneo\Product;

use App\Component\Akeneo\AkeneoHelper;
use Shopsys\FrameworkBundle\Component\Money\Money;

class AkeneoProductHelper
{
    public const TYPE_INT = 'int';
    public const TYPE_FLOAT = 'float';
    public const TYPE_DOUBLE = 'double';
    public const TYPE_BOOLEAN = 'bool';

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
        foreach (array_keys($productData) as $key) {
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
        foreach (array_keys($productData) as $key) {
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
     * @param bool[] $productData
     * @param array|null $akeneoData
     * @param bool $default
     * @return bool[]
     */
    public static function mapDomainDataBool(array $productData, ?array $akeneoData, bool $default): array
    {
        foreach (array_keys($productData) as $key) {
            $productData[$key] = $default;
        }

        if ($akeneoData === null) {
            return $productData;
        }

        foreach ($akeneoData as $data) {
            $domainId = AkeneoHelper::findEshopDomainIdByAkeneoLocale($data['locale']);
            if ($domainId !== null) {
                $productData[$domainId] = $data['data'];
            }
        }

        return $productData;
    }

    /**
     * @param int[]|null[] $productData
     * @param array $akeneoData
     * @return int[]|null[]
     */
    public static function mapDomainDataInt(array $productData, array $akeneoData): array
    {
        foreach (array_keys($productData) as $key) {
            $productData[$key] = null;
        }

        foreach ($akeneoData as $data) {
            $domainId = AkeneoHelper::findEshopDomainIdByAkeneoLocale($data['locale']);
            if ($domainId) {
                $productData[$domainId] = (int)$data['data'];
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
        foreach (array_keys($productData) as $key) {
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
        foreach (array_keys($productData) as $key) {
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

    /**
     * @param array $productData
     * @param mixed|null $akeneoData
     * @return array
     */
    public static function mapDataToAllDomains(array $productData, $akeneoData): array
    {
        foreach (array_keys($productData) as $key) {
            $productData[$key] = null;
        }

        if ($akeneoData === null) {
            return $productData;
        }

        foreach (array_keys($productData) as $key) {
            $productData[$key] = $akeneoData;
        }

        return $productData;
    }

    /**
     * @param string|null $data
     * @param string $type
     * @return mixed
     */
    public static function convertStringToType(?string $data, string $type)
    {
        if ($data === null) {
            return $data;
        }

        switch ($type) {
            case self::TYPE_INT:
                return (int)$data;
            case self::TYPE_FLOAT:
            case self::TYPE_DOUBLE:
                return (float)$data;
            case self::TYPE_BOOLEAN:
                return filter_var($data, FILTER_VALIDATE_BOOLEAN);
            default:
                return $data;
        }
    }
}
