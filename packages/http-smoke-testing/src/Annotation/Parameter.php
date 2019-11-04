<?php

declare(strict_types=1);

namespace Shopsys\HttpSmokeTesting\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 */
class Parameter
{
    /**
     * @var string
     * @Required()
     */
    public $name;

    /**
     * @var string
     * @Required()
     */
    public $value;
}
