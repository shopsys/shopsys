<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Test;

use Closure;

class ReferenceDataAccessor
{
    public function __construct(
        public readonly string $reference,
        public readonly Closure $callback,
        public readonly ?int $domainId = null,
    ) {
    }
}
