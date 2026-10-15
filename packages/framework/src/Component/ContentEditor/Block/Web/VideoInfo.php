<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web;

class VideoInfo
{
    public function __construct(
        public readonly string $provider,
        public readonly string $videoId,
        public readonly string $embedUrl,
    ) {
    }
}
