<?php

declare(strict_types=1);

use Shopsys\FrameworkBundle\Component\Translation\Translator;

/**
 * @param string $id
 * @param array $parameters
 * @param string|null $domain Translation domain (default is "messages")
 * @param string|null $locale
 * @return string
 */
function t(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
{
    return Translator::staticTrans($id, $parameters, $domain, $locale);
}
