<?php

declare(strict_types=1);

namespace App\Component\Packetery;

use App\Component\Packetery\Packet\PacketAttributes;
use Twig\Environment as TwigEnvironment;

class PacketeryRenderer
{
    public const TEMPLATE_FILE_PATH = 'packetery/createPacket.xml.twig';

    /**
     * @var \Twig\Environment
     */
    private TwigEnvironment $twig;

    /**
     * PacketeryRenderer constructor.
     *
     * @param \Twig\Environment $twig
     */
    public function __construct(TwigEnvironment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param \App\Component\Packetery\Packet\PacketAttributes $packetAttributes
     * @param \App\Component\Packetery\PacketeryConfig $packeteryConfig
     * @return string
     */
    public function getPacketXml(PacketAttributes $packetAttributes, PacketeryConfig $packeteryConfig): string
    {
        $template = $this->twig->load(self::TEMPLATE_FILE_PATH);
        return $template->render([
            'config' => $packeteryConfig,
            'packet' => $packetAttributes,
        ]);
    }
}
