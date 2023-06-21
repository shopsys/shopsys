<?php

declare(strict_types=1);

namespace App\Component\Image;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\Image\Image as BaseImage;

/**
 * @ORM\Table(name="images", indexes={@ORM\Index(columns={"entity_name", "entity_id", "type"})})
 * @ORM\Entity
 */
class Image extends BaseImage
{
    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $akeneoCode;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $akeneoImageType;

    /**
     * @return string|null
     */
    public function getAkeneoCode(): ?string
    {
        return $this->akeneoCode;
    }

    /**
     * @param string|null $akeneoCode
     */
    public function setAkeneoCode(?string $akeneoCode): void
    {
        $this->akeneoCode = $akeneoCode;
    }

    /**
     * @return string|null
     */
    public function getAkeneoImageType(): ?string
    {
        return $this->akeneoImageType;
    }

    /**
     * @param string|null $akeneoImageType
     */
    public function setAkeneoImageType(?string $akeneoImageType): void
    {
        $this->akeneoImageType = $akeneoImageType;
    }

    /**
     * @param string|null $friendlyUrlSlug
     * @return string
     */
    public function getSeoFilename(?string $friendlyUrlSlug): string
    {
        $slug = '';

        if ($friendlyUrlSlug !== null) {
            $slug = $friendlyUrlSlug . '_';
        }

        return  $slug . $this->id . '.' . $this->extension;
    }
}
