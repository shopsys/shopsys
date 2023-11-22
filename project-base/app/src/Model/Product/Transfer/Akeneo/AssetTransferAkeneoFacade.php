<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use Akeneo\PimEnterprise\ApiClient\AkeneoPimEnterpriseClientInterface;
use Psr\Http\Message\ResponseInterface;

class AssetTransferAkeneoFacade
{
    /**
     * @param \Akeneo\PimEnterprise\ApiClient\AkeneoPimEnterpriseClientInterface $akeneoClient
     */
    public function __construct(private AkeneoPimEnterpriseClientInterface $akeneoClient)
    {
    }

    /**
     * @return \Akeneo\PimEnterprise\ApiClient\Api\AssetManager\AssetApiInterface
     */
    private function getAssetManagerApiEndpoint(): \Akeneo\PimEnterprise\ApiClient\Api\AssetManager\AssetApiInterface
    {
        return $this->akeneoClient->getAssetManagerApi();
    }

    /**
     * @param string $assetFamilyCode
     * @param string $imageCode
     * @return mixed[]
     */
    public function getImageData(string $assetFamilyCode, string $imageCode): array
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
