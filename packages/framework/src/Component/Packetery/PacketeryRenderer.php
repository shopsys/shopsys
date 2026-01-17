<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Packetery;

use Shopsys\FrameworkBundle\Component\Packetery\Packet\PacketAttributes;
use Twig\Environment as TwigEnvironment;

class PacketeryRenderer
{
    protected const string TEMPLATE_FILE_PATH = '@ShopsysFramework/Packetery/createPacket.xml.twig';

    public function __construct(
        protected readonly TwigEnvironment $twig,
    ) {
    }

    public function getPacketXml(PacketAttributes $packetAttributes, PacketeryConfig $packeteryConfig): string
    {
        return $this->twig->load(static::TEMPLATE_FILE_PATH)->render([
            'config' => $packeteryConfig,
            'packet' => $packetAttributes,
        ]);
    }
}
