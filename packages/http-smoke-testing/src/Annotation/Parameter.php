<?php

declare(strict_types=1);

namespace Shopsys\HttpSmokeTesting\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 */
class Parameter
{
    /** @var string */
    public $name;

    /** @var string */
    public $value;
}
