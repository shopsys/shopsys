<?php

namespace Shopsys\ProductFeed\GoogleBundle\Form;

use Shopsys\Plugin\PluginCrudExtensionInterface;
use Shopsys\ProductFeed\GoogleBundle\Model\Product\GoogleProductDomainData;
use Shopsys\ProductFeed\GoogleBundle\Model\Product\GoogleProductDomainDataFactoryInterface;
use Shopsys\ProductFeed\GoogleBundle\Model\Product\GoogleProductDomainFacade;
use Symfony\Contracts\Translation\TranslatorInterface;

class GoogleProductCrudExtension implements PluginCrudExtensionInterface
{
    /**
     * @param \Symfony\Contracts\Translation\TranslatorInterface $translator
     * @param \Shopsys\ProductFeed\GoogleBundle\Model\Product\GoogleProductDomainFacade $googleProductDomainFacade
     * @param \Shopsys\ProductFeed\GoogleBundle\Model\Product\GoogleProductDomainDataFactoryInterface $googleProductDomainDataFactory
     */
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly GoogleProductDomainFacade $googleProductDomainFacade,
        private readonly GoogleProductDomainDataFactoryInterface $googleProductDomainDataFactory,
    ) {
    }

    /**
     * @return string
     */
    public function getFormTypeClass()
    {
        return GoogleProductFormType::class;
    }

    /**
     * @return string
     */
    public function getFormLabel()
    {
        return $this->translator->trans('Google Shopping product feed');
    }

    /**
     * @param int $productId
     * @return array
     */
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
     * @param \Shopsys\ProductFeed\GoogleBundle\Model\Product\GoogleProductDomainData $googleProductDomainData
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
    public function removeData($productId)
    {
        $this->googleProductDomainFacade->delete($productId);
    }
}
