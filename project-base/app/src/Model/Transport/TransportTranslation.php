<?php

declare(strict_types=1);

namespace App\Model\Transport;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Transport\TransportTranslation as BaseTransportTranslation;

/**
 * @ORM\Table(name="transport_translations")
 * @ORM\Entity
 * @property \App\Model\Transport\Transport $translatable
 */
class TransportTranslation extends BaseTransportTranslation
{
}
