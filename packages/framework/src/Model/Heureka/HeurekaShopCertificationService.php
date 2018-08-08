<?php

namespace Shopsys\FrameworkBundle\Model\Heureka;

use Heureka\ShopCertification;

class HeurekaShopCertificationService
{
    public function isDomainLocaleSupported(string $locale): bool
    {
        try {
            $this->getLanguageIdByLocale($locale);
            return true;
        } catch (\Shopsys\FrameworkBundle\Model\Heureka\Exception\LocaleNotSupportedException $ex) {
            return false;
        }
    }

    public function getLanguageIdByLocale(string $locale): int
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

    public function getServerNameByLocale(string $locale): ?string
    {
        if ($locale === 'cs') {
            return 'Heureka.cz';
        } elseif ($locale === 'sk') {
            return 'Heureka.sk';
        }
    }
}
