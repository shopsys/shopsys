<?php

declare(strict_types=1);

namespace App\Component\Test;

use Attribute;

/**
 * Attribute to mark properties for automatic service injection in tests.
 * Works as a modern replacement for @inject annotation.
 * 
 * @IgnoreAnnotation("inject")
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Inject
{
    /**
     * @param string|null $serviceId Optional explicit service ID to inject
     */
    public function __construct(
        private readonly ?string $serviceId = null,
    ) {
    }

    public function getServiceId(): ?string
    {
        return $this->serviceId;
    }
}