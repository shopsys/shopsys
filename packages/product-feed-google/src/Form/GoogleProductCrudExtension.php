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

    #[Override]
    public function getFormTypeClass(): string
    {
        return GoogleProductFormType::class;
    }

    #[Override]
    public function getFormLabel(): string
    {
        return $this->translator->trans('Google Shopping product feed');
    }

    #[Override]
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

    #[Override]
    public function saveData(int $productId, mixed $data): void
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

    private function setGoogleProductDomainDataProperty(
        GoogleProductDomainData $googleProductDomainData,
        string $propertyName,
        bool $propertyValue,
    ): void {
        switch ($propertyName) {
            case 'show':
                $googleProductDomainData->show = $propertyValue;

                break;
        }
    }

    #[Override]
    public function removeData(int $productId): void
    {
        $this->googleProductDomainFacade->delete($productId);
    }
}
