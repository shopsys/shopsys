<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Heureka;

use Heureka\ShopCertification;
use Shopsys\FrameworkBundle\Model\Heureka\Exception\LocaleNotSupportedException;

class HeurekaShopCertificationLocaleHelper
{
    /**
     * @param string $locale
     * @return bool
     */
    public function isDomainLocaleSupported($locale)
    {
        try {
            $this->getLanguageIdByLocale($locale);

            return true;
        } catch (LocaleNotSupportedException $ex) {
            return false;
        }
    }

    /**
     * @param string $locale
     * @return int
     */
    public function getLanguageIdByLocale($locale)
    {
        $supportedLanguagesByLocale = [
            'cs' => ShopCertification::HEUREKA_CZ,
            'sk' => ShopCertification::HEUREKA_SK,
        ];

        if (array_key_exists($locale, $supportedLanguagesByLocale)) {
            return $supportedLanguagesByLocale[$locale];
        }

        $message = 'Locale "' . $locale . '" is not supported.';

        throw new LocaleNotSupportedException($message);
    }

    /**
     * @param string $locale
     * @return string|null
     */
    public function getServerNameByLocale($locale)
    {
        if ($locale === 'cs') {
            return 'Heureka.cz';
        }

        if ($locale === 'sk') {
            return 'Heureka.sk';
        }

        return null;
    }
}
