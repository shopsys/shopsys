<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityExtension;

use Doctrine\ORM\Mapping\AssociationMapping;
use Doctrine\ORM\Mapping\ClassMetadata as BaseClassMetadata;
use Override;

class ClassMetadata extends BaseClassMetadata
{
    #[Override]
    protected function _storeAssociationMapping(AssociationMapping $assocMapping): void
    {
        unset($this->associationMappings[$assocMapping->fieldName]);

        parent::_storeAssociationMapping($assocMapping);
    }

    #[Override]
    public function mapField(array $mapping): void
    {
        $fieldName = $mapping['fieldName'];

        if (isset($this->fieldMappings[$fieldName])) {
            $columnName = $this->fieldMappings[$fieldName]->columnName;
            unset($this->fieldMappings[$fieldName], $this->columnNames[$fieldName], $this->fieldNames[$columnName]);
        }

        parent::mapField($mapping);
    }
}
