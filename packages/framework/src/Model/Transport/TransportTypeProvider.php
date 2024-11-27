<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

class TransportTypeProvider
{
    /**
     * @param iterable<\Shopsys\FrameworkBundle\Model\Transport\AbstractTransportTypeEnum> $transportTypeEnums
     */
    public function __construct(
        protected readonly iterable $transportTypeEnums,
    ) {
    }

    /**
     * @return array
     */
    public function getAllIndexedByTranslations(): array
    {
        $allIndexedByTranslations = [];

        foreach ($this->transportTypeEnums as $transportTypeEnum) {
            $allIndexedByTranslations = array_merge($allIndexedByTranslations, $transportTypeEnum->getAllIndexedByTranslations());
        }

        return $allIndexedByTranslations;
    }

    /**
     * @return array<string, string>
     */
    public function getAllEnabledInCartIndexedByTranslations(): array
    {
        $enabledInCartCases = [];

        foreach ($this->transportTypeEnums as $transportTypeEnum) {
            $enabledInCartCases = array_merge($enabledInCartCases, $transportTypeEnum->getEnabledInCartCases());
        }

        return $enabledInCartCases;
    }
}
