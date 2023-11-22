<?php

declare(strict_types=1);

namespace App\Component\Akeneo;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class AkeneoHelper
{
    public const ESHOP_LOCALES_BY_AKENEO_LOCALES = [
        'cs_CZ' => 'cs',
        'sk_SK' => 'sk',
    ];

    /**
     * @param string $akeneoLocale
     * @return string|null
     */
    public static function findEshopLocaleByAkeneoLocale(string $akeneoLocale): ?string
    {
        if (array_key_exists($akeneoLocale, self::ESHOP_LOCALES_BY_AKENEO_LOCALES)) {
            return self::ESHOP_LOCALES_BY_AKENEO_LOCALES[$akeneoLocale];
        }

        return null;
    }

    /**
     * @param string $akeneoLocale
     * @return int|null
     */
    public static function findEshopDomainIdByAkeneoLocale(string $akeneoLocale): ?int
    {
        $domains = [
            'cs_CZ' => Domain::FIRST_DOMAIN_ID,
            'sk_SK' => Domain::SECOND_DOMAIN_ID,
        ];

        if (array_key_exists($akeneoLocale, $domains)) {
            return $domains[$akeneoLocale];
        }

        return null;
    }

    /**
     * @param string $currencyCode
     * @return int|null
     */
    public static function findEshopDomainIdByCurrencyCode(string $currencyCode): ?int
    {
        $domains = [
            'CZK' => Domain::FIRST_DOMAIN_ID,
            'EUR' => Domain::SECOND_DOMAIN_ID,
        ];

        if (array_key_exists($currencyCode, $domains)) {
            return $domains[$currencyCode];
        }

        return null;
    }

    /**
     * @param mixed[] $itemData
     * @param array|null $akeneoData
     * @return mixed[]
     */
    public static function mapLocalizedLabels(array $itemData, ?array $akeneoData): array
    {
        foreach (array_keys($itemData) as $key) {
            $itemData[$key] = null;
        }

        $labels = $akeneoData['labels'] ?? null;

        if ($labels === null) {
            return $itemData;
        }

        foreach ($labels as $akeneoLocale => $akeneoString) {
            $locale = self::findEshopLocaleByAkeneoLocale($akeneoLocale);

            if ($locale) {
                $itemData[$locale] = $akeneoString;
            }
        }

        return $itemData;
    }
}
