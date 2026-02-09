<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\DataTransformer;

use InvalidArgumentException;
use Override;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleRegistryInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\Section\AbstractRoleSectionProvider;
use Symfony\Component\Form\DataTransformerInterface;

final class RolesGridDataTransformer implements DataTransformerInterface
{
    /**
     * @param class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext> $context
     * @param string[] $excludedRoles
     */
    public function __construct(
        private readonly RoleRegistryInterface $roleRegistry,
        private readonly string $context,
        private readonly array $excludedRoles = [],
        private readonly bool $shouldGroupBySections = true,
    ) {
    }

    /**
     * Transforms role identifiers array to section structure for subforms
     *
     * @param array<string>|mixed $value Array of role identifiers (e.g., ['ROLE_ORDER_VIEW', 'ROLE_PRODUCT_FULL'])
     * @return array<string, mixed> Form structure [sectionKey] = role identifiers
     */
    #[Override]
    public function transform(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $identifiersBySection = [];

        foreach ($value as $identifier) {
            if (!is_string($identifier)) {
                continue;
            }

            try {
                $role = $this->roleRegistry->getRole($identifier, $this->context);
            } catch (InvalidArgumentException) {
                // Skip invalid roles
                continue;
            }

            if (in_array($role->getConstant(), $this->excludedRoles, true)) {
                continue;
            }

            $section = $this->shouldGroupBySections && $role->getRoleSection() !== null ? $role->getRoleSection() : AbstractRoleSectionProvider::OTHER;
            $identifiersBySection[$section][] = $identifier;
        }

        return $identifiersBySection;
    }

    /**
     * Transforms form data back to role identifiers array - collects from subforms
     *
     * @param array<string, mixed>|mixed $value Form data from subforms
     * @return array<string> Array of role identifiers (e.g., ['ROLE_ORDER_VIEW', 'ROLE_PRODUCT_FULL'])
     */
    #[Override]
    public function reverseTransform(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $result = [];

        foreach ($value as $subformData) {
            if (is_array($subformData)) {
                $result = array_merge($result, $subformData);
            }
        }

        return $result;
    }
}
