<?php

declare(strict_types=1);

namespace App\Component\Image;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Image\Image;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade as BaseImageFacade;

class ImageFacade extends BaseImageFacade
{
    /**
     * @var string|null
     */
    private $cdnDomain;

    /**
     * @param string $cdnDomain
     */
    public function setCdnDomain(string $cdnDomain): void
    {
        // When you do not want to use CDN, it is used value '//' as workaround by https://github.com/symfony/symfony/issues/28391
        if (empty(trim($cdnDomain, '/')) === false) {
            $this->cdnDomain = $cdnDomain;
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param \Shopsys\FrameworkBundle\Component\Image\Image|Object $imageOrEntity
     * @param string|null $sizeName
     * @param string|null $type
     * @return string
     */
    public function getImageUrl(DomainConfig $domainConfig, $imageOrEntity, $sizeName = null, $type = null)
    {
        $imageUrl = parent::getImageUrl($domainConfig, $imageOrEntity, $sizeName, $type);

        return $this->replaceDomainUrlByCdnDomain($imageUrl, $domainConfig);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param int $id
     * @param string $extension
     * @param string $entityName
     * @param string|null $type
     * @param string|null $sizeName
     * @return string
     */
    public function getImageUrlFromAttributes(
        DomainConfig $domainConfig,
        int $id,
        string $extension,
        string $entityName,
        ?string $type,
        ?string $sizeName = null
    ): string {
        $imageUrl = parent::getImageUrlFromAttributes($domainConfig, $id, $extension, $entityName, $type, $sizeName);

        return $this->replaceDomainUrlByCdnDomain($imageUrl, $domainConfig);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param int $id
     * @param string $extension
     * @param string $entityName
     * @param string|null $type
     * @param string|null $sizeName
     * @return \Shopsys\FrameworkBundle\Component\Image\AdditionalImageData[]
     */
    public function getAdditionalImagesDataFromAttributes(
        DomainConfig $domainConfig,
        int $id,
        string $extension,
        string $entityName,
        ?string $type,
        ?string $sizeName = null
    ): array {
        $additionalImagesData = parent::getAdditionalImagesDataFromAttributes($domainConfig, $id, $extension, $entityName, $type, $sizeName);

        return $this->replaceDomainUrlByCdnDomainInAdditionalImagesData($additionalImagesData, $domainConfig);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param int $additionalSizeIndex
     * @param \Shopsys\FrameworkBundle\Component\Image\Image $image
     * @param string|null $sizeName
     * @return string
     */
    protected function getAdditionalImageUrl(DomainConfig $domainConfig, int $additionalSizeIndex, Image $image, ?string $sizeName)
    {
        $imageUrl = parent::getAdditionalImageUrl($domainConfig, $additionalSizeIndex, $image, $sizeName);

        return $this->replaceDomainUrlByCdnDomain($imageUrl, $domainConfig);
    }

    /**
     * @param string $imageUrl
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return string
     */
    private function replaceDomainUrlByCdnDomain(string $imageUrl, DomainConfig $domainConfig): string
    {
        if ($this->cdnDomain === null) {
            return $imageUrl;
        }

        return str_replace($domainConfig->getUrl(), $this->cdnDomain, $imageUrl);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\AdditionalImageData[] $additionalImagesData
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Component\Image\AdditionalImageData[]
     */
    private function replaceDomainUrlByCdnDomainInAdditionalImagesData(array $additionalImagesData, DomainConfig $domainConfig): array
    {
        if ($this->cdnDomain === null) {
            return $additionalImagesData;
        }

        foreach ($additionalImagesData as $additionalImageData) {
            $additionalImageData->url = $this->replaceDomainUrlByCdnDomain($additionalImageData->url, $domainConfig);
        }

        return $additionalImagesData;
    }
}
