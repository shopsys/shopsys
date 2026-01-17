<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductVideo;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;

/**
 * @ORM\Table(name="product_video_translations")
 * @ORM\Entity
 */
class ProductVideoTranslations
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideo
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideo")
     * @ORM\JoinColumn(name="product_video", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $productVideo;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $description;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $locale;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideo
     */
    public function getProductVideo()
    {
        return $this->productVideo;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideo $productVideo
     */
    public function setProductVideo($productVideo): void
    {
        $this->productVideo = $productVideo;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description): void
    {
        $this->description = TransformStringHelper::getTrimmedStringOrNullOnEmpty($description);
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale($locale): void
    {
        $this->locale = $locale;
    }
}
