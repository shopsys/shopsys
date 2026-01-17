<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\GoogleBundle\Form;

use Override;
use Shopsys\Plugin\PluginCrudExtensionInterface;
use Shopsys\ProductFeed\GoogleBundle\Model\Product\GoogleProductDomainData;
use Shopsys\ProductFeed\GoogleBundle\Model\Product\GoogleProductDomainDataFactory;
use Shopsys\ProductFeed\GoogleBundle\Model\Product\GoogleProductDomainFacade;
use Symfony\Contracts\Translation\TranslatorInterface;

class GoogleProductCrudExtension implements PluginCrudExtensionInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly GoogleProductDomainFacade $googleProductDomainFacade,
        private readonly GoogleProductDomainDataFactory $googleProductDomainDataFactory,
    ) {
    }

    /**
     * @return string
     */
    #[Override]
    public function getFormTypeClass()
    {
        return GoogleProductFormType::class;
    }

    /**
     * @return string
     */
    #[Override]
    public function getFormLabel()
    {
        return $this->translator->trans('Google Shopping product feed');
    }

    /**
     * @param int $productId
     * @return array
     */
    #[Override]
    public function getData($productId)
    {
        $googleProductDomains = $this->googleProductDomainFacade->findByProductId($productId);

        $pluginData = [
            'show' => [],
        ];

        foreach ($googleProductDomains as $googleProductDomain) {
            $pluginData['show'][$googleProductDomain->getDomainId()] = $googleProductDomain->getShow();
        }

        return $pluginData;
    }

    /**
     * @param int $productId
     * @param array $data
     */
    #[Override]
    public function saveData($productId, $data)
    {
        $googleProductDomainsDataIndexedByDomainId = [];

        foreach ($data as $productAttributeName => $productAttributeValuesByDomainIds) {
            foreach ($productAttributeValuesByDomainIds as $domainId => $productAttributeValue) {
                if (!array_key_exists($domainId, $googleProductDomainsDataIndexedByDomainId)) {
                    $googleProductDomainData = $this->googleProductDomainDataFactory->create();
                    $googleProductDomainData->domainId = $domainId;

                    $googleProductDomainsDataIndexedByDomainId[$domainId] = $googleProductDomainData;
                }

                $this->setGoogleProductDomainDataProperty(
                    $googleProductDomainsDataIndexedByDomainId[$domainId],
                    $productAttributeName,
                    $productAttributeValue,
                );
            }
        }

        $this->googleProductDomainFacade->saveGoogleProductDomainsForProductId(
            $productId,
            $googleProductDomainsDataIndexedByDomainId,
        );
    }

    /**
     * @param string $propertyName
     * @param bool $propertyValue
     */
    private function setGoogleProductDomainDataProperty(
        GoogleProductDomainData $googleProductDomainData,
        $propertyName,
        $propertyValue,
    ) {
        switch ($propertyName) {
            case 'show':
                $googleProductDomainData->show = $propertyValue;

                break;
        }
    }

    /**
     * @param int $productId
     */
    #[Override]
    public function removeData($productId)
    {
        $this->googleProductDomainFacade->delete($productId);
    }
}
