<?php

declare(strict_types=1);

namespace Shopsys\HttpSmokeTesting\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 */
class Parameter
{
    #[Required]
    public string $name;

    #[Required]
    public string $value;
}
