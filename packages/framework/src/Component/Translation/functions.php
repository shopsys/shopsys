<?php

use Shopsys\FrameworkBundle\Component\Translation\Translator;

/**
 * @param string $id
 * @param array $parameters
 * @param string|null $translationDomain
 * @param string|null $locale
 * @return string
 */
function t(string $id, array $parameters = [], ?string $translationDomain = null, ?string $locale = null): string
{
    return Translator::staticTrans($id, $parameters, $translationDomain, $locale);
}
