<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\Security\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class CsrfProtection
{
}
