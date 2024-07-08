<?php

declare(strict_types=1);

namespace App\Model\ProductVideo;

use Doctrine\ORM\Mapping as ORM;

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
    protected int $id;

    /**
     * @var \App\Model\ProductVideo\ProductVideo
     * @ORM\ManyToOne(targetEntity="App\Model\ProductVideo\ProductVideo")
     * @ORM\JoinColumn(name="product_video", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private ProductVideo $productVideo;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private string $description;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private string $locale;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \App\Model\ProductVideo\ProductVideo
     */
    public function getProductVideo(): ProductVideo
    {
        return $this->productVideo;
    }

    /**
     * @param \App\Model\ProductVideo\ProductVideo $productVideo
     */
    public function setProductVideo(ProductVideo $productVideo): void
    {
        $this->productVideo = $productVideo;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }
}
