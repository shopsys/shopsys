<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig\FileThumbnail;

class FileThumbnailInfo
{
    protected ?string $iconType = null;

    protected ?string $imageUri = null;

    /**
     * @param string|null $iconType
     * @param string|null $imageUri
     */
    public function __construct(?string $iconType, ?string $imageUri = null)
    {
        $this->iconType = $iconType;
        $this->imageUri = $imageUri;
    }

    /**
     * @return string|null
     */
    public function getIconType(): ?string
    {
        return $this->iconType;
    }

    /**
     * @return string|null
     */
    public function getImageUri(): ?string
    {
        return $this->imageUri;
    }
}
