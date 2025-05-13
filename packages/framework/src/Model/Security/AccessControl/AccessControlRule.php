<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Security\AccessControl;

use Attribute;

/**
 * @see https://symfony.com/doc/current/security/access_control.html
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class AccessControlRule
{
    /**
     * @param string[] $roles
     * @param string[] $methods
     * @param string[] $attributes
     * @param string|null $host
     * @param string|null $ips
     * @param string|null $port
     * @param string|null $requestMatcher
     * @param string|null $allowIf
     * @param string|null $requiresChannel
     */
    public function __construct(
        public readonly array $roles = [],
        public readonly array $methods = [],
        public readonly array $attributes = [],
        public readonly ?string $host = null,
        public readonly ?string $ips = null,
        public readonly ?string $port = null,
        public readonly ?string $requestMatcher = null,
        public readonly ?string $allowIf = null,
        public readonly ?string $requiresChannel = null,
    ) {
    }

    /**
     * Used for caching into the PHP file using var_export
     *
     * @see \Shopsys\FrameworkBundle\Model\Security\AccessControl\RouteAccessControlDataProvider::findAll()
     * @param array $array
     * @return self
     */
    public static function __set_state(array $array): self
    {
        return new self(
            $array['roles'] ?? [],
            $array['methods'] ?? [],
            $array['attributes'] ?? [],
            $array['host'] ?? null,
            $array['ips'] ?? null,
            $array['port'] ?? null,
            $array['requestMatcher'] ?? null,
            $array['allowIf'] ?? null,
            $array['requiresChannel'] ?? null,
        );
    }
}
