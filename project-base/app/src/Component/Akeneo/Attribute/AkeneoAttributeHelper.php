<?php

declare(strict_types=1);

namespace App\Component\Akeneo\Attribute;

use App\Component\Akeneo\AkeneoHelper;

class AkeneoAttributeHelper
{
    /**
     * @param mixed[] $attributeData
     * @param array|null $akeneoData
     * @return mixed[]
     */
    public static function mapLocalizedDataString(array $attributeData, ?array $akeneoData): array
    {
        foreach (array_keys($attributeData) as $key) {
            $attributeData[$key] = null;
        }

        if ($akeneoData === null) {
            return $attributeData;
        }

        foreach ($akeneoData as $akeneoLocale => $data) {
            $locale = AkeneoHelper::findEshopLocaleByAkeneoLocale($akeneoLocale);

            if ($locale) {
                $attributeData[$locale] = $data;
            }
        }

        return $attributeData;
    }
}
