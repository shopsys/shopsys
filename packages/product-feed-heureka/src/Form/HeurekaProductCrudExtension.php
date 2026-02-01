<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle\Form;

use Override;
use Shopsys\Plugin\PluginCrudExtensionInterface;
use Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomainDataFactory;
use Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomainFacade;
use Symfony\Contracts\Translation\TranslatorInterface;

class HeurekaProductCrudExtension implements PluginCrudExtensionInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly HeurekaProductDomainFacade $heurekaProductDomainFacade,
        private readonly HeurekaProductDomainDataFactory $heurekaProductDomainDataFactory,
    ) {
    }

    #[Override]
    public function getFormTypeClass(): string
    {
        return HeurekaProductFormType::class;
    }

    #[Override]
    public function getFormLabel(): string
    {
        return $this->translator->trans('Heureka.cz product feed');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getData(int $productId): array
    {
        $heurekaProductDomains = $this->heurekaProductDomainFacade->findByProductId($productId);

        $pluginData = [
            'cpc' => [],
        ];

        foreach ($heurekaProductDomains as $heurekaProductDomain) {
            $pluginData['cpc'][$heurekaProductDomain->getDomainId()] = $heurekaProductDomain->getCpc();
        }

        return $pluginData;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function saveData(int $productId, mixed $data): void
    {
        $heurekaProductDomainsData = [];

        if (array_key_exists('cpc', $data)) {
            foreach ($data['cpc'] as $domainId => $cpc) {
                $heurekaProductDomainData = $this->heurekaProductDomainDataFactory->create();
                $heurekaProductDomainData->domainId = $domainId;
                $heurekaProductDomainData->cpc = $cpc;

                $heurekaProductDomainsData[] = $heurekaProductDomainData;
            }
        }
        $this->heurekaProductDomainFacade->saveHeurekaProductDomainsForProductId(
            $productId,
            $heurekaProductDomainsData,
        );
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function removeData(int $productId): void
    {
        $this->heurekaProductDomainFacade->delete($productId);
    }
}
