<?php

declare(strict_types=1);

namespace App\Form\Admin;

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
