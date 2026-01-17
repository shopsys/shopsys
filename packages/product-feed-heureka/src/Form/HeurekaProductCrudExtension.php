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

    /**
     * @return string
     */
    #[Override]
    public function getFormTypeClass()
    {
        return HeurekaProductFormType::class;
    }

    /**
     * @return string
     */
    #[Override]
    public function getFormLabel()
    {
        return $this->translator->trans('Heureka.cz product feed');
    }

    /**
     * @param int $productId
     * @return array
     */
    #[Override]
    public function getData($productId)
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
     * @param int $productId
     * @param array $data
     */
    #[Override]
    public function saveData($productId, $data): void
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
     * @param int $productId
     */
    #[Override]
    public function removeData($productId): void
    {
        $this->heurekaProductDomainFacade->delete($productId);
    }
}
