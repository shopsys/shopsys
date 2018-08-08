<?php

namespace Shopsys\FrameworkBundle\Twig\FileThumbnail;

class FileThumbnailInfo
{
    /**
     * @var string|null
     */
    private $iconType;

    /**
     * @var string|null
     */
    private $imageUri;

    public function __construct(?string $iconType, ?string $imageUri = null)
    {
        $this->iconType = $iconType;
        $this->imageUri = $imageUri;
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
