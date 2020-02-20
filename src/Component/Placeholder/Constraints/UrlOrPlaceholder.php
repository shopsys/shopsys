<?php

declare(strict_types = 1);

namespace App\Component\Placeholder\Constraints;

use Symfony\Component\Validator\Constraint;

class UrlOrPlaceholder extends Constraint
{
    public const CHECK_DNS_TYPE_ANY = 'ANY';
    public const CHECK_DNS_TYPE_NONE = false;
    public const CHECK_DNS_TYPE_A = 'A';
    public const CHECK_DNS_TYPE_A6 = 'A6';
    public const CHECK_DNS_TYPE_AAAA = 'AAAA';
    public const CHECK_DNS_TYPE_CNAME = 'CNAME';
    public const CHECK_DNS_TYPE_MX = 'MX';
    public const CHECK_DNS_TYPE_NAPTR = 'NAPTR';
    public const CHECK_DNS_TYPE_NS = 'NS';
    public const CHECK_DNS_TYPE_PTR = 'PTR';
    public const CHECK_DNS_TYPE_SOA = 'SOA';
    public const CHECK_DNS_TYPE_SRV = 'SRV';
    public const CHECK_DNS_TYPE_TXT = 'TXT';

    public const INVALID_URL_OR_PLACEHOLDER_ERROR = '57c2f299-1154-4870-89bb-ef3b1f5ad229';

    protected static $errorNames = [
        self::INVALID_URL_OR_PLACEHOLDER_ERROR => 'INVALID_URL_OR_PLACEHOLDER_ERROR',
    ];

    public $notAllowedPlaceholderMessage = 'Makro {{ placeholderName }} není povoleno používat v tomto poli';

    public $message = 'Tato hodnota není validní URL nebo známé URL makro';

    public $dnsMessage = 'The host could not be resolved.';

    public $protocols = ['http', 'https'];

    public $allowedPlaceholders = [];

    public $checkDns = self::CHECK_DNS_TYPE_NONE;
}
