<?php

declare(strict_types=1);

namespace App\Component\Akeneo\Transfer\MediaFiles;

use Akeneo\PimEnterprise\ApiClient\AkeneoPimEnterpriseClientInterface;
use Psr\Http\Message\ResponseInterface;

class MediaFilesTransferAkeneoFacade
{
    /**
     * @var \Akeneo\PimEnterprise\ApiClient\AkeneoPimEnterpriseClientInterface
     */
    private $akeneoClient;

    /**
     * @param \Akeneo\PimEnterprise\ApiClient\AkeneoPimEnterpriseClientInterface $akeneoClient
     */
    public function __construct(AkeneoPimEnterpriseClientInterface $akeneoClient)
    {
        $this->akeneoClient = $akeneoClient;
    }

    /**
     * @param string $code
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getProductMediaFile(string $code): ResponseInterface
    {
        return $this->akeneoClient->getProductMediaFileApi()->download($code);
    }
}
