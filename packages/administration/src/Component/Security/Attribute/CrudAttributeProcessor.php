<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Security\Attribute;

use InvalidArgumentException;
use LogicException;
use ReflectionClass;
use ReflectionMethod;
use Shopsys\AdministrationBundle\Component\Attributes\CrudAction;
use Shopsys\FrameworkBundle\Component\Reflection\ReflectionHelper;

/**
 * Processes security attributes of the actions of a CRUD controller (built-in and custom, wherever they are handled).
 * The Can* attributes without a role use the ForRole attribute of the handling class and then the role of the CRUD controller.
 */
final class CrudAttributeProcessor extends AbstractAttributeProcessor
{
    /**
     * @param string $roleConstant role of the CRUD controller the action belongs to
     * @return list<\Shopsys\AdministrationBundle\Component\Security\AccessControl\AccessControlRuleData>
     */
    public function processCrudAction(ReflectionClass $class, ReflectionMethod $method, string $roleConstant): array
    {
        if (!$this->isCrudAction($class, $method)) {
            throw new InvalidArgumentException(sprintf(
                '%s::%s() is not a CRUD action, it has no %s attribute.',
                $class->getName(),
                $method->getName(),
                CrudAction::class,
            ));
        }

        $rules = $this->collectRules($class, $method, $this->getClassRole($class) ?? $roleConstant);

        if ($rules === []) {
            throw new LogicException(sprintf(
                'CRUD action %s::%s() must be guarded by an access control attribute (CanView, CanEdit, CanCreate, CanDelete, RequirePermission, RequireRole or SuperAdminOnly).',
                $class->getName(),
                $method->getName(),
            ));
        }

        return $rules;
    }

    /**
     * A class-level CrudAction attribute declares the __invoke() method as the action
     */
    private function isCrudAction(ReflectionClass $class, ReflectionMethod $method): bool
    {
        if (ReflectionHelper::getMethodAttributes($method, CrudAction::class) !== []) {
            return true;
        }

        return $method->getName() === '__invoke' && $class->getAttributes(CrudAction::class) !== [];
    }
}
