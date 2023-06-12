<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

class ParameterValuesViewData
{
    /**
     * @var string[]
     */
    private array $valueTexts;

    /**
     * @param string $parameterName
     * @param string|null $parameterGroupName
     * @param string|null $parameterGroupAkeneoCode
     * @param string|null $unitName
     */
    public function __construct(
        private string $parameterName,
        private ?string $parameterGroupName,
        private ?string $parameterGroupAkeneoCode,
        private ?string $unitName,
    ) {
        $this->valueTexts = [];
    }

    /**
     * @param string $valueText
     */
    public function addParameterValueText(string $valueText): void
    {
        $this->valueTexts[] = $valueText;
    }

    /**
     * @return string
     */
    public function getParameterName(): string
    {
        return $this->parameterName;
    }

    /**
     * @return string|null
     */
    public function getParameterGroupName(): ?string
    {
        return $this->parameterGroupName;
    }

    /**
     * @return string|null
     */
    public function getParameterGroupAkeneoCode(): ?string
    {
        return $this->parameterGroupAkeneoCode;
    }

    /**
     * @return string|null
     */
    public function getUnitName(): ?string
    {
        return $this->unitName;
    }

    /**
     * @return string[]
     */
    public function getValueTexts(): array
    {
        return $this->valueTexts;
    }

    /**
     * @return string[]
     */
    public function getValueTextsWithUnit(): array
    {
        if ($this->unitName === null) {
            return $this->valueTexts;
        }

        return array_map(function (string $valueText): string {
            return $valueText . ' ' . $this->unitName;
        }, $this->valueTexts);
    }
}
