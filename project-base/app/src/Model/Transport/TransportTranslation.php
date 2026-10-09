<?php

declare(strict_types=1);

namespace App\Model\Transport;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableChild;
use Shopsys\FrameworkBundle\Model\Transport\TransportTranslation as BaseTransportTranslation;
use Shopsys\McpAttributes\Attribute\AsMcpInheritedColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

/**
 * @property \App\Model\Transport\Transport $translatable
 */
#[AsMcpTable]
#[AsMcpInheritedColumn(fieldName: 'id')]
#[AsMcpInheritedColumn(fieldName: 'locale')]
#[LoggableChild]
#[ORM\Table(name: 'transport_translations')]
#[ORM\Entity]
class TransportTranslation extends BaseTransportTranslation
{
}
