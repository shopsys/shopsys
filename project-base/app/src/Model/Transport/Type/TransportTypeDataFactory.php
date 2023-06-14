<?php

declare(strict_types=1);

namespace App\Model\Transport\Type;

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
     * @return \App\Model\Transport\Type\TransportTypeData
     */
    protected function createInstance(): TransportTypeData
    {
        return new TransportTypeData();
    }

    /**
     * @return \App\Model\Transport\Type\TransportTypeData
     */
    public function create(): TransportTypeData
    {
        $transportTypeData = $this->createInstance();
        $this->fillNew($transportTypeData);

        return $transportTypeData;
    }

    /**
     * @param \App\Model\Transport\Type\TransportTypeData $transportTypeData
     */
    private function fillNew(TransportTypeData $transportTypeData): void
    {
        foreach ($this->domain->getAllLocales() as $locale) {
            $transportTypeData->names[$locale] = null;
        }
    }

    /**
     * @param \App\Model\Transport\Type\TransportType $transportType
     * @return \App\Model\Transport\Type\TransportTypeData
     */
    public function createFromTransportType(TransportType $transportType): TransportTypeData
    {
        $transportTypeData = $this->createInstance();
        $this->fillFromTransportType($transportTypeData, $transportType);

        return $transportTypeData;
    }

    /**
     * @param \App\Model\Transport\Type\TransportTypeData $transportTypeData
     * @param \App\Model\Transport\Type\TransportType $transportType
     */
    private function fillFromTransportType(TransportTypeData $transportTypeData, TransportType $transportType): void
    {
        /** @var \App\Model\Transport\Type\TransportTypeTranslation[] $translations */
        $translations = $transportType->getTranslations();

        $names = [];

        foreach ($translations as $translation) {
            $names[$translation->getLocale()] = $translation->getName();
        }

        $transportTypeData->names = $names;
        $transportTypeData->code = $transportType->getCode();
    }
}
