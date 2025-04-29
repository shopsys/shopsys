<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Security\AccessControl;

class RouteAccessControlData
{
    /**
     * @param string $routeName
     * @param \Shopsys\FrameworkBundle\Model\Security\AccessControl\AccessControlRule $accessControlRule
     */
    public function __construct(
        public readonly string $routeName,
        public readonly AccessControlRule $accessControlRule,
    ) {
    }

    /**
     * @param array $array
     * @return self
     */
    public static function fromArray(array $array): self
    {
        return new self(
            $array['routeName'] ?? '',
            AccessControlRule::fromArray($array['accessControlRule'] ?? []),
        );
    }
}
