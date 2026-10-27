<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\PromoCode;

use Override;
use Shopsys\FrameworkBundle\Form\InteractiveCollectionType;
use Symfony\Component\Form\AbstractType;

final class PromoCodeLimitCollectionType extends AbstractType
{
    #[Override]
    public function getParent(): string
    {
        return InteractiveCollectionType::class;
    }
}
