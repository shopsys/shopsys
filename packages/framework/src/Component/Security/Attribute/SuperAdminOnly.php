<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Attribute;

use Attribute;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;

/**
 * Restricts access to super administrators only
 *
 * Can be used on class level to apply to all methods:
 * #[SuperAdminOnly]
 * class SuperAdminController extends AdminBaseController
 *
 * Or on method level:
 * #[SuperAdminOnly]
 * #[SuperAdminOnly([HttpMethod::POST])]
 * #[SuperAdminOnly(['POST', HttpMethod::PUT])]
 * public function dangerousAction(): Response
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final readonly class SuperAdminOnly
{
    /**
     * @param array<string|\Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod> $methods
     */
    public function __construct(
        private array $methods = [],
    ) {
    }

    /**
     * Get methods as normalized HttpMethod enum array
     *
     * @return \Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod[]
     */
    public function getMethods(): array
    {
        return HttpMethod::validateMethods($this->methods);
    }
}
