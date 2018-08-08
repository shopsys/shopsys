<?php

namespace Shopsys\ProductFeed\GoogleBundle\Form;

use Shopsys\Plugin\PluginCrudExtensionInterface;
use Shopsys\ProductFeed\GoogleBundle\Model\Product\GoogleProductDomainData;
use Shopsys\ProductFeed\GoogleBundle\Model\Product\GoogleProductDomainDataFactoryInterface;
use Shopsys\ProductFeed\GoogleBundle\Model\Product\GoogleProductDomainFacade;
use Symfony\Component\Translation\TranslatorInterface;

class GoogleProductCrudExtension implements PluginCrudExtensionInterface
{

    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @var \Shopsys\ProductFeed\GoogleBundle\Model\Product\GoogleProductDomainFacade
     */
    private $googleProductDomainFacade;

    /**
     * @var \Shopsys\ProductFeed\GoogleBundle\Model\Product\GoogleProductDomainDataFactoryInterface
     */
    private $googleProductDomainDataFactory;

    public function __construct(
        TranslatorInterface $translator,
        GoogleProductDomainFacade $googleProductDomainFacade,
        GoogleProductDomainDataFactoryInterface $googleProductDomainDataFactory
    ) {
        $this->translator = $translator;
        $this->googleProductDomainFacade = $googleProductDomainFacade;
        $this->googleProductDomainDataFactory = $googleProductDomainDataFactory;
    }

    public function getFormTypeClass(): string
    {
        return GoogleProductFormType::class;
    }

    public function getFormLabel(): string
    {
        return $this->translator->trans('Google Shopping product feed');
    }
    
    public function getData(int $productId): array
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
    
    public function saveData(int $productId, array $data): void
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
                    $productAttributeValue
                );
            }
        }

        $this->googleProductDomainFacade->saveGoogleProductDomainsForProductId(
            $productId,
            $googleProductDomainsDataIndexedByDomainId
        );
    }
    
    private function setGoogleProductDomainDataProperty(
        GoogleProductDomainData $googleProductDomainData,
        string $propertyName,
        string $propertyValue
    ): void {
        switch ($propertyName) {
            case 'show':
                $googleProductDomainData->show = $propertyValue;
                break;
        }
    }
    
    public function removeData(int $productId): void
    {
        $this->googleProductDomainFacade->delete($productId);
    }
}
