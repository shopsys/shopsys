<?php

declare(strict_types=1);

namespace App\Model\Transport;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Transport\TransportTranslation as BaseTransportTranslation;

/**
 * @property \App\Model\Transport\Transport $translatable
 */
#[ORM\Table(name: 'transport_translations')]
#[ORM\Entity]
class TransportTranslation extends BaseTransportTranslation
{
}
