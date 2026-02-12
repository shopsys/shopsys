<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityExtensionListener;

class OverwritableEntityExtensionListener extends EntityExtensionListener
{
    /**
     * @param string[] $entityExtensionMap
     */
    public function overwriteEntityExtensionMap(array $entityExtensionMap): void
    {
        $this->setEntityExtensionMap($entityExtensionMap);
    }
}
