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
     * Used for caching into the PHP file using var_export
     *
     * @see \Shopsys\FrameworkBundle\Model\Security\AccessControl\RouteAccessControlDataProvider::findAll()
     * @param array{routeName: string, accessControlRule: \Shopsys\FrameworkBundle\Model\Security\AccessControl\AccessControlRule} $array
     * @return self
     */
    public static function __set_state(array $array): self
    {
        return new self(
            $array['routeName'],
            $array['accessControlRule'],
        );
    }
}
