<?php

declare(strict_types=1);

namespace Tests\App\Test;

final class ProductQueryPattern
{
    public const string PRODUCTS_WITH_DOMAINS_AND_FLAGS_LOAD = '~FROM products \w+ INNER JOIN product_domains \w+ .*LEFT JOIN product_domain_flags~s';
}
