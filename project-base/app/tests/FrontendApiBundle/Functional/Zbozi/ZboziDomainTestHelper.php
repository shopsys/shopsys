<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Zbozi;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Locale\LocaleHelper;

final class ZboziDomainTestHelper
{
    public static function findFirstNonCsDomainId(Domain $domain): ?int
    {
        foreach ($domain->getAll() as $domainConfig) {
            if ($domainConfig->getLocale() !== LocaleHelper::LOCALE_CS) {
                return $domainConfig->getId();
            }
        }

        return null;
    }

    public static function findFirstCsDomainId(Domain $domain): ?int
    {
        foreach ($domain->getAll() as $domainConfig) {
            if ($domainConfig->getLocale() === LocaleHelper::LOCALE_CS) {
                return $domainConfig->getId();
            }
        }

        return null;
    }
}
