<?php

use Shopsys\FrameworkBundle\Component\Translation\Translator;

/**
 * @param string $id
 * @param array $parameters
 * @param string|null $domain
 * @param string|null $locale
 * @return string
 */
function t($id, array $parameters = [], $domain = null, $locale = null)
{
    return Translator::staticTrans($id, $parameters, $domain, $locale);
}
