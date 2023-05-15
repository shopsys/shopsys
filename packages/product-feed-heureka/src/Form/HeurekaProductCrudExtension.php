<?php

namespace Shopsys\ProductFeed\HeurekaBundle\Form;

use Shopsys\Plugin\PluginCrudExtensionInterface;
use Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomainDataFactoryInterface;
use Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomainFacade;
use Symfony\Contracts\Translation\TranslatorInterface;

class HeurekaProductCrudExtension implements PluginCrudExtensionInterface
{
    /**
     * @param \Symfony\Contracts\Translation\TranslatorInterface $translator
     * @param \Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomainFacade $heurekaProductDomainFacade
     * @param \Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomainDataFactoryInterface $heurekaProductDomainDataFactory
     */
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly HeurekaProductDomainFacade $heurekaProductDomainFacade,
        private readonly HeurekaProductDomainDataFactoryInterface $heurekaProductDomainDataFactory,
    ) {
    }

    /**
     * @return string
     */
    public function getFormTypeClass()
    {
        return HeurekaProductFormType::class;
    }

    /**
     * @return string
     */
    public function getFormLabel()
    {
        return $this->translator->trans('Heureka.cz product feed');
    }

    /**
     * @param int $productId
     * @return array
     */
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
    public function saveData($productId, $data)
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
    public function removeData($productId)
    {
        $this->heurekaProductDomainFacade->delete($productId);
    }
}
