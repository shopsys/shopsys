<?php

namespace Shopsys\FrameworkBundle\Model\Heureka;

use Heureka\ShopCertification;

class HeurekaShopCertificationService
{
    /**
     * @param string $locale
     */
    public function isDomainLocaleSupported($locale): bool
    {
        try {
            $this->getLanguageIdByLocale($locale);
            return true;
        } catch (\Shopsys\FrameworkBundle\Model\Heureka\Exception\LocaleNotSupportedException $ex) {
            return false;
        }
    }

    /**
     * @param string $locale
     */
    public function getLanguageIdByLocale($locale): int
    {
        $supportedLanguagesByLocale = [
            'cs' => ShopCertification::HEUREKA_CZ,
            'sk' => ShopCertification::HEUREKA_SK,
        ];

        if (array_key_exists($locale, $supportedLanguagesByLocale)) {
            return $supportedLanguagesByLocale[$locale];
        }

        $message = 'Locale "' . $locale . '" is not supported.';
        throw new \Shopsys\FrameworkBundle\Model\Heureka\Exception\LocaleNotSupportedException($message);
    }

    /**
     * @param string $locale
     */
    public function getServerNameByLocale($locale): ?string
    {
        if ($locale === 'cs') {
            return 'Heureka.cz';
        } elseif ($locale === 'sk') {
            return 'Heureka.sk';
        }
    }
}
