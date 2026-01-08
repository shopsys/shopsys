<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use Akeneo\Pim\ApiClient\AkeneoPimClientInterface;
use Psr\Http\Message\ResponseInterface;

class AssetTransferAkeneoFacade
{
    /**
     * @param \Akeneo\Pim\ApiClient\AkeneoPimClientInterface $akeneoClient
     */
    public function __construct(private AkeneoPimClientInterface $akeneoClient)
    {
    }

    /**
     * @return \Akeneo\Pim\ApiClient\Api\AssetManager\AssetApiInterface
     */
    private function getAssetManagerApiEndpoint()
    {
        return $this->akeneoClient->getAssetManagerApi();
    }

    /**
     * @param string $assetFamilyCode
     * @param string $imageCode
     * @return array
     */
    public function getImageData($assetFamilyCode, $imageCode): array
    {
        return $this->getAssetManagerApiEndpoint()->get($assetFamilyCode, $imageCode);
    }

    /**
     * @param string $code
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getAssetMediaFileFromApi(string $code): ResponseInterface
    {
        return $this->akeneoClient->getAssetMediaFileApi()->download($code);
    }
}
