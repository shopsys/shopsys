<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Security;

/**
 * @see https://symfony.com/doc/current/security/access_control.html
 */
class AccessControlRule
{
    /**
     * @param string $path
     * @param string[] $roles
     * @param string[] $methods
     * @param string[] $attributes
     * @param string|null $host
     * @param string|null $ips
     * @param string|null $port
     * @param string|null $requestMatcher
     * @param string|null $routeName
     * @param string|null $allowIf
     * @param string|null $requiresChannel
     */
    public function __construct(
        public string $path,
        public array $roles = [],
        public array $methods = [],
        public array $attributes = [],
        public ?string $host = null,
        public ?string $ips = null,
        public ?string $port = null,
        public ?string $requestMatcher = null,
        public ?string $routeName = null,
        public ?string $allowIf = null,
        public ?string $requiresChannel = null,
    ) {
    }
}
