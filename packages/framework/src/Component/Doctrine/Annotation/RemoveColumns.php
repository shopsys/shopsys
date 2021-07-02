<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Annotation\Target("CLASS")
 */
class RemoveColumns
{
    /**
     * @var string[]
     * @Annotation\Required()
     */
    public array $propertyNames;
}
