<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Complaint\Status;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

final class ComplaintItemsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getParent(): string
    {
        return CollectionType::class;
    }
}
