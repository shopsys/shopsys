<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle\Form;

use Override;
use Shopsys\Plugin\PluginCrudExtensionInterface;
use Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomainData;
use Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomainDataFactory;
use Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomainFacade;
use Symfony\Contracts\Translation\TranslatorInterface;

class ZboziProductCrudExtension implements PluginCrudExtensionInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly ZboziProductDomainFacade $zboziProductDomainFacade,
        private readonly ZboziProductDomainDataFactory $zboziProductDomainDataFactory,
    ) {
    }

    #[Override]
    public function getFormTypeClass(): string
    {
        return ZboziProductFormType::class;
    }

    #[Override]
    public function getFormLabel(): string
    {
        return $this->translator->trans('Zbozi.cz product feed');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getData(int $productId): array
    {
        $zboziProductDomains = $this->zboziProductDomainFacade->findByProductId($productId);

        return $zboziProductDomains !== null && count(
            $zboziProductDomains,
        ) > 0 ? $this->getZboziProductDomainsAsPluginDataArray(
            $zboziProductDomains,
        ) : [];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function saveData(int $productId, mixed $data): void
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
                    $productAttributeValue,
                );
            }
        }

        $this->zboziProductDomainFacade->saveZboziProductDomainsForProductId(
            $productId,
            $zboziProductDomainsDataIndexedByDomainId,
        );
    }

    private function setZboziProductDomainDataProperty(
        ZboziProductDomainData $zboziProductDomainData,
        string $propertyName,
        mixed $propertyValue,
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
     * {@inheritdoc}
     */
    #[Override]
    public function removeData(int $productId): void
    {
        $this->zboziProductDomainFacade->delete($productId);
    }
}
