<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\Security\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Annotation\Target("METHOD")
 */
class CsrfProtection extends Annotation
{
}
