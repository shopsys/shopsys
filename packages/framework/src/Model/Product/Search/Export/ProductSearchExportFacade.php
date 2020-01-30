<?php

namespace Shopsys\FrameworkBundle\Model\Product\Search\Export;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class ProductSearchExportFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Search\Export\ProductSearchExporter
     */
    protected $exporter;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\Export\ProductSearchExporter $exporter
     */
    public function __construct(Domain $domain, ProductSearchExporter $exporter)
    {
        $this->domain = $domain;
        $this->exporter = $exporter;
    }

    /**
     * @param int[] $productIds
     */
    public function exportIds(array $productIds): void
    {
        foreach ($this->domain->getAll() as $domain) {
            $domainId = $domain->getId();
            $locale = $domain->getLocale();

            $this->exporter->exportIds($domainId, $locale, $productIds);
        }
    }
}
