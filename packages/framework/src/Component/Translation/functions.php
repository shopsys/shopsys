<?php

use Shopsys\FrameworkBundle\Component\Translation\Translator;

/**
 * @param string $id
 * @param string|null $domain
 * @param string|null $locale
 */
function t($id, array $parameters = [], $domain = null, $locale = null): string
{
    return Translator::staticTrans($id, $parameters, $domain, $locale);
}

/**
 * @param string $id
 * @param int $number
 * @param string|null $domain
 * @param string|null $locale
 */
function tc($id, $number, array $parameters = [], $domain = null, $locale = null): string
{
    return Translator::staticTransChoice($id, $number, $parameters, $domain, $locale);
}
