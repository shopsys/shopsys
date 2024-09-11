<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Complaint\Status;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ComplaintItemsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }
}
