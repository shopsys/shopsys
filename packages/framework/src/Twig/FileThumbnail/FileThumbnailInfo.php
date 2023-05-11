<?php

namespace Shopsys\FrameworkBundle\Twig\FileThumbnail;

class FileThumbnailInfo
{
    protected ?string $iconType = null;

    protected ?string $imageUri = null;

    /**
     * @param string|null $iconType
     * @param string|null $imageUri
     */
    public function __construct($iconType, $imageUri = null)
    {
        $this->iconType = $iconType;
        $this->imageUri = $imageUri;
    }

    /**
     * @return string|null
     */
    public function getIconType()
    {
        return $this->iconType;
    }

    /**
     * @return string|null
     */
    public function getImageUri()
    {
        return $this->imageUri;
    }
}
