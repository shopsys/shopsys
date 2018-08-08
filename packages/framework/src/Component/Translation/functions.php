<?php

use Shopsys\FrameworkBundle\Component\Translation\Translator;

/**
 * @param string|null $domain
 * @param string|null $locale
 */
function t(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
{
    return Translator::staticTrans($id, $parameters, $domain, $locale);
}

/**
 * @param string|null $domain
 * @param string|null $locale
 */
function tc(string $id, int $number, array $parameters = [], ?string $domain = null, ?string $locale = null): string
{
    return Translator::staticTransChoice($id, $number, $parameters, $domain, $locale);
}
