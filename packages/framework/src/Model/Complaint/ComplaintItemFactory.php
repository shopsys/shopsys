<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class ComplaintItemFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData $complaintItemData
     * @return \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItem
     */
    public function create(
        ComplaintItemData $complaintItemData,
    ): ComplaintItem {
        $entityClassName = $this->entityNameResolver->resolve(ComplaintItem::class);

        return new $entityClassName($complaintItemData);
    }
}
