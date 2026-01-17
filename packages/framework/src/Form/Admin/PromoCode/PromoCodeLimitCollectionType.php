<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\PromoCode;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

final class PromoCodeLimitCollectionType extends AbstractType
{
    #[Override]
    public function getParent(): string
    {
        return CollectionType::class;
    }
}
