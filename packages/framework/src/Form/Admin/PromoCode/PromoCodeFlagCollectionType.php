<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\PromoCode;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class PromoCodeFlagCollectionType extends AbstractType
{
    /**
     * @return string|null
     */
    public function getParent(): ?string
    {
        return CollectionType::class;
    }
}
