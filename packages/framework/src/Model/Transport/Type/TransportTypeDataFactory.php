<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport\Type;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class TransportTypeDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected Domain $domain,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\Type\TransportTypeData
     */
    protected function createInstance(): TransportTypeData
    {
        return new TransportTypeData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\Type\TransportTypeData
     */
    public function create(): TransportTypeData
    {
        $transportTypeData = $this->createInstance();
        $this->fillNew($transportTypeData);

        return $transportTypeData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Type\TransportTypeData $transportTypeData
     */
    protected function fillNew(TransportTypeData $transportTypeData): void
    {
        foreach ($this->domain->getAllLocales() as $locale) {
            $transportTypeData->names[$locale] = null;
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Type\TransportType $transportType
     * @return \Shopsys\FrameworkBundle\Model\Transport\Type\TransportTypeData
     */
    public function createFromTransportType(TransportType $transportType): TransportTypeData
    {
        $transportTypeData = $this->createInstance();
        $this->fillFromTransportType($transportTypeData, $transportType);

        return $transportTypeData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Type\TransportTypeData $transportTypeData
     * @param \Shopsys\FrameworkBundle\Model\Transport\Type\TransportType $transportType
     */
    protected function fillFromTransportType(TransportTypeData $transportTypeData, TransportType $transportType): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Transport\Type\TransportTypeTranslation[] $translations */
        $translations = $transportType->getTranslations();

        $names = [];

        foreach ($translations as $translation) {
            $names[$translation->getLocale()] = $translation->getName();
        }

        $transportTypeData->names = $names;
        $transportTypeData->code = $transportType->getCode();
    }
}
