<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Attribute;

use Attribute;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;

/**
 * Allows public access to the controller action (no authentication required)
 *
 * Can be used on class level to apply to all methods:
 * #[PublicAccess]
 * class PublicController extends AdminBaseController
 *
 * Or on method level:
 * #[PublicAccess]
 * #[PublicAccess([HttpMethod::GET])]
 * #[PublicAccess(['GET', HttpMethod::HEAD])]
 * public function healthCheckAction(): Response
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final readonly class PublicAccess
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
