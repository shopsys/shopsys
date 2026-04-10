<?php

declare(strict_types=1);

namespace App\Model\Transport;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Transport\TransportTranslation as BaseTransportTranslation;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

/**
 * @property \App\Model\Transport\Transport $translatable
 */
#[AsMcpTable]
#[AsMcpColumn(fieldName: 'id')]
#[AsMcpColumn(fieldName: 'locale')]
#[ORM\Table(name: 'transport_translations')]
#[ORM\Entity]
class TransportTranslation extends BaseTransportTranslation
{
}
