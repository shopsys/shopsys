<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo;

class HreflangLink
{
    /**
     * @param string $hreflang
     * @param string $href
     */
    public function __construct(
        public readonly string $hreflang,
        public readonly string $href,
    ) {
    }
}
