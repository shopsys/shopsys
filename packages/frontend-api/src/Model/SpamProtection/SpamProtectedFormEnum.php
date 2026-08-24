<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\SpamProtection;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

/**
 * The case value is a part of the rate limiter key, so every form counts its own quota.
 */
class SpamProtectedFormEnum extends AbstractEnum
{
    public const string CONTACT_FORM = 'contact-form';
}
