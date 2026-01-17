<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\DataTransformer;

use InvalidArgumentException;
use Override;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleRegistryInterface;
use Symfony\Component\Form\DataTransformerInterface;

final class RoleSectionDataTransformer implements DataTransformerInterface
{
    /**
     * @param array<\Shopsys\FrameworkBundle\Component\Security\Role\Role> $sectionRoles
     * @param class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext> $context
     */
    public function __construct(
        private readonly array $sectionRoles,
        private readonly RoleRegistryInterface $roleRegistry,
        private readonly string $context,
    ) {
    }

    /**
     * Transforms role identifiers to section-level data structure
     *
     * @param array<string>|mixed $value Array of role identifiers from main form
     * @return array<string, mixed> Section data structure [roleConstant] = role data
     */
    #[Override]
    public function transform(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $sectionData = [];

        // Filter identifiers for each role in this section
        foreach ($this->sectionRoles as $role) {
            $roleIdentifiers = array_filter($value, function ($identifier) use ($role) {
                if (!is_string($identifier)) {
                    return false;
                }

                try {
                    $identifierRole = $this->roleRegistry->getRole($identifier, $this->context);

                    return $identifierRole->getConstant() === $role->getConstant();
                } catch (InvalidArgumentException) {
                    return false;
                }
            });

            $sectionData[$role->getConstant()] = array_values($roleIdentifiers);
        }

        return $sectionData;
    }

    /**
     * Transforms section form data back to role identifiers
     *
     * @param array<string, mixed>|mixed $value Section form data [roleConstant] = role form data
     * @return array<string> Array of role identifiers for this section
     */
    #[Override]
    public function reverseTransform(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $result = [];

        // Collect identifiers from all role rows in this section
        foreach ($value as $roleConstant => $roleData) {
            if (is_array($roleData)) {
                $result = array_merge($result, $roleData);
            }
        }

        return $result;
    }
}
