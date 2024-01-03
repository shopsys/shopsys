<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet;

class CollectionChanges
{
    public string $dataType = 'Collection';

    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\ResolvedChanges[]
     */
    public array $insertedItems = [];

    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\ResolvedChanges[]
     */
    public array $deletedItems = [];
}
