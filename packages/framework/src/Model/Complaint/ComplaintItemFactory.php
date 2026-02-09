<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class ComplaintItemFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(
        ComplaintItemData $complaintItemData,
    ): ComplaintItem {
        $entityClassName = $this->entityNameResolver->resolve(ComplaintItem::class);

        return new $entityClassName($complaintItemData);
    }
}
