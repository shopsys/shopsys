<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use Akeneo\Pim\ApiClient\Api\AttributeApiInterface;
use Akeneo\Pim\ApiClient\Api\AttributeGroupApiInterface;
use Akeneo\PimEnterprise\ApiClient\AkeneoPimEnterpriseClientInterface;
use Generator;

class FlagTransferAkeneoFacade
{
    public const FLAG_GROUP_NAME = 'Flag';

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
     * @return \Akeneo\Pim\ApiClient\Api\AttributeGroupApiInterface
     */
    private function getGroupApiEndpoint(): AttributeGroupApiInterface
    {
        return $this->akeneoClient->getAttributeGroupApi();
    }

    /**
     * @return \Akeneo\Pim\ApiClient\Api\AttributeApiInterface
     */
    private function getAttributeApiEndpoint(): AttributeApiInterface
    {
        return $this->akeneoClient->getAttributeApi();
    }

    /**
     * @return \Generator
     */
    public function getAllFlags(): Generator
    {
        $flags = $this->getGroupApiEndpoint()->get(self::FLAG_GROUP_NAME);

        foreach ($flags['attributes'] as $flagCode) {
            yield $this->getAttributeApiEndpoint()->get($flagCode);
        }
    }
}
