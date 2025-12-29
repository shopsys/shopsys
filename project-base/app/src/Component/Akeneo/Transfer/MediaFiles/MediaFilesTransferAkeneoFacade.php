<?php

declare(strict_types=1);

namespace App\Component\Akeneo\Transfer\MediaFiles;

use Akeneo\Pim\ApiClient\AkeneoPimClientInterface;
use Psr\Http\Message\ResponseInterface;

class MediaFilesTransferAkeneoFacade
{
    /**
     * @param \Akeneo\Pim\ApiClient\AkeneoPimClientInterface $akeneoClient
     */
    public function __construct(private AkeneoPimClientInterface $akeneoClient)
    {
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
