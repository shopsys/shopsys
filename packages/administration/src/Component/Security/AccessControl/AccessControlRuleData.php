<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Security\AccessControl;

use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;

/**
 * Access control rule resolved from the security attributes, not yet bound to a Role of the registry
 * (see AccessControlRuleFactory), so it can be created without any service, e.g. at build time
 */
final readonly class AccessControlRuleData
{
    /**
     * @param string $roleIdentifier role constant optionally suffixed with the permission (see RoleIdentifierHelper)
     * @param array<\Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod|string> $httpMethods empty for all methods
     */
    public function __construct(
        public string $roleIdentifier,
        public array $httpMethods = [],
    ) {
    }

    /**
     * @return array{roleIdentifier: string, httpMethods: string[]}
     */
    public function toArray(): array
    {
        return [
            'roleIdentifier' => $this->roleIdentifier,
            'httpMethods' => array_map(
                static fn (HttpMethod|string $httpMethod): string => $httpMethod instanceof HttpMethod ? $httpMethod->value : $httpMethod,
                $this->httpMethods,
            ),
        ];
    }

    /**
     * @param array{roleIdentifier: string, httpMethods: string[]} $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data['roleIdentifier'], $data['httpMethods']);
    }
}
