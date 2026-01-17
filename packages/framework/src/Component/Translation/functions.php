<?php

declare(strict_types=1);

use Shopsys\FrameworkBundle\Component\Translation\Translator;

/**
 * @param string|null $domain Translation domain (default is "messages")
 */
function t(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
{
    return Translator::staticTrans($id, $parameters, $domain, $locale);
}
