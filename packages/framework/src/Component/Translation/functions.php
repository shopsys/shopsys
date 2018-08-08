<?php

use Shopsys\FrameworkBundle\Component\Translation\Translator;

function t(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
{
    return Translator::staticTrans($id, $parameters, $domain, $locale);
}

function tc(string $id, int $number, array $parameters = [], ?string $domain = null, ?string $locale = null): string
{
    return Translator::staticTransChoice($id, $number, $parameters, $domain, $locale);
}
