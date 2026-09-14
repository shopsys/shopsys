<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Security\Attribute;

use ReflectionClass;
use ReflectionMethod;

/**
 * Processes security attributes of a controller method, the Can* attributes without a role use the ForRole attribute of the class
 */
final class AttributeProcessor extends AbstractAttributeProcessor
{
    /**
     * @return list<\Shopsys\AdministrationBundle\Component\Security\AccessControl\AccessControlRuleData>
     */
    public function processMethod(ReflectionClass $class, ReflectionMethod $method): array
    {
        return $this->collectRules($class, $method, $this->getClassRole($class));
    }
}
