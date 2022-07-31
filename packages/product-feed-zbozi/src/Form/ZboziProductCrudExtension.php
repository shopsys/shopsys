<?php

namespace Shopsys\ProductFeed\ZboziBundle\Form;

use Shopsys\Plugin\PluginCrudExtensionInterface;
use Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomainData;
use Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomainDataFactoryInterface;
use Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomainFacade;
use Symfony\Contracts\Translation\TranslatorInterface;

class ZboziProductCrudExtension implements PluginCrudExtensionInterface
{
    /**
     * @var \Symfony\Contracts\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @var \Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomainFacade
     */
    private $zboziProductDomainFacade;

    /**
     * @var \Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomainDataFactoryInterface
     */
    private $zboziProductDomainDataFactory;

    /**
     * @param \Symfony\Contracts\Translation\TranslatorInterface $translator
     * @param \Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomainFacade $zboziProductDomainFacade
     * @param \Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomainDataFactoryInterface $zboziProductDomainDataFactory
     */
    public function __construct(
        TranslatorInterface $translator,
        ZboziProductDomainFacade $zboziProductDomainFacade,
        ZboziProductDomainDataFactoryInterface $zboziProductDomainDataFactory
    ) {
        $this->translator = $translator;
        $this->zboziProductDomainFacade = $zboziProductDomainFacade;
        $this->zboziProductDomainDataFactory = $zboziProductDomainDataFactory;
    }

    /**
     * @return string
     */
    public function getFormTypeClass(): string
    {
        return ZboziProductFormType::class;
    }

    /**
     * @return string
     */
    public function getFormLabel(): string
    {
        return $this->translator->trans('Zbozi.cz product feed');
    }

    /**
     * @param int $productId
     * @return array<string, mixed>|array{show: array<int, bool>, cpc: array<int, ?\Shopsys\FrameworkBundle\Component\Money\Money>, cpc_search: array<int, ?\Shopsys\FrameworkBundle\Component\Money\Money>}
     */
    public function getData($productId): array
    {
        $zboziProductDomains = $this->zboziProductDomainFacade->findByProductId($productId);

        if (count($zboziProductDomains) === 0) {
            return [];
        }

        return $this->getZboziProductDomainsAsPluginDataArray($zboziProductDomains);
    }

    /**
     * @param int $productId
     * @param array<string, array<int, mixed>> $data
     */
    public function saveData($productId, $data): void
    {
        $zboziProductDomainsDataIndexedByDomainId = [];

        foreach ($data as $productAttributeName => $productAttributeValuesByDomainIds) {
            foreach ($productAttributeValuesByDomainIds as $domainId => $productAttributeValue) {
                if (!array_key_exists($domainId, $zboziProductDomainsDataIndexedByDomainId)) {
                    $zboziProductDomainsData = $this->zboziProductDomainDataFactory->create();
                    $zboziProductDomainsData->domainId = $domainId;

                    $zboziProductDomainsDataIndexedByDomainId[$domainId] = $zboziProductDomainsData;
                }

                $this->setZboziProductDomainDataProperty(
                    $zboziProductDomainsDataIndexedByDomainId[$domainId],
                    $productAttributeName,
                    $productAttributeValue
                );
            }
        }

        $this->zboziProductDomainFacade->saveZboziProductDomainsForProductId(
            $productId,
            $zboziProductDomainsDataIndexedByDomainId
        );
    }

    /**
     * @param \Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomainData $zboziProductDomainData
     * @param string $propertyName
     * @param bool|\Shopsys\FrameworkBundle\Component\Money\Money|null $propertyValue
     */
    private function setZboziProductDomainDataProperty(
        ZboziProductDomainData $zboziProductDomainData,
        string $propertyName,
        $propertyValue
    ): void {
        switch ($propertyName) {
            case 'show':
                $zboziProductDomainData->show = $propertyValue;
                break;
            case 'cpc':
                $zboziProductDomainData->cpc = $propertyValue;
                break;
            case 'cpc_search':
                $zboziProductDomainData->cpcSearch = $propertyValue;
                break;
        }
    }

    /**
     * @param \Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomain[] $zboziProductDomains
     * @return array{show: array<int, bool>, cpc: array<int, ?\Shopsys\FrameworkBundle\Component\Money\Money>, cpc_search: array<int, ?\Shopsys\FrameworkBundle\Component\Money\Money>}
     */
    private function getZboziProductDomainsAsPluginDataArray(array $zboziProductDomains): array
    {
        $pluginData = [
            'show' => [],
            'cpc' => [],
            'cpc_search' => [],
        ];

        foreach ($zboziProductDomains as $zboziProductDomain) {
            $pluginData['show'][$zboziProductDomain->getDomainId()] = $zboziProductDomain->getShow();
            $pluginData['cpc'][$zboziProductDomain->getDomainId()] = $zboziProductDomain->getCpc();
            $pluginData['cpc_search'][$zboziProductDomain->getDomainId()] = $zboziProductDomain->getCpcSearch();
        }

        return $pluginData;
    }

    /**
     * @param int $productId
     */
    public function removeData($productId): void
    {
        $this->zboziProductDomainFacade->delete($productId);
    }
}
