<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig\FileThumbnail;

class FileThumbnailInfo
{
    public function __construct(protected ?string $iconType = null, protected ?string $imageUri = null)
    {
    }

    public function getIconType(): ?string
    {
        return $this->iconType;
    }

    public function getImageUri(): ?string
    {
        return $this->imageUri;
    }
}
