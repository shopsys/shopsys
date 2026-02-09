<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Localization\Exception;

use Exception;
use RuntimeException;

class AdminLocaleNotFoundException extends RuntimeException
{
    /**
     * @param string[] $possibleLocales
     */
    public function __construct(
        ?string $adminLocale = null,
        protected readonly array $possibleLocales = [],
        ?Exception $previous = null,
    ) {
        if ($adminLocale === null) {
            $message = 'There is no locale registered for the administration. Check your "shopsys.allowed_admin_locales" parameter configuration.';
        } else {
            $message = sprintf(
                'You tried to set "%1$s" locale for the administration, but you have registered only ["%2$s"].'
                . ' Either register "%1$s" as a locale with some domain, set "%1$s" as allowed admin locale, or use one of ["%2$s"] as administration locale.',
                $adminLocale,
                implode('","', $possibleLocales),
            );
        }

        parent::__construct($message, 0, $previous);
    }

    /**
     * @return string[]
     */
    public function getPossibleLocales(): array
    {
        return $this->possibleLocales;
    }
}
