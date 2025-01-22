<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

class ImportPriceListResult
{
    /**
     * @var string[]
     */
    protected array $errors = [];

    /**
     * @var string[]
     */
    protected array $warnings = [];

    protected int $importedCount = 0;

    protected int $priceListId;

    protected string $priceListName;

    /**
     * @param int $line
     * @param string $error
     */
    public function addError(int $line, string $error): void
    {
        $this->errors[] = $this->formatMessage($line, $error);
    }

    /**
     * @param int $line
     * @param string $warning
     */
    public function addWarning(int $line, string $warning): void
    {
        $this->warnings[] = $this->formatMessage($line, $warning);
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return string[]
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * @return int
     */
    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function increaseSuccessfulCount(): void
    {
        $this->importedCount++;
    }

    /**
     * @return bool
     */
    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    /**
     * @return bool
     */
    public function hasWarnings(): bool
    {
        return count($this->warnings) > 0;
    }

    /**
     * @param int $line
     * @param string $message
     * @return string
     */
    public function formatMessage(int $line, string $message): string
    {
        return t('Line:') . '#' . $line . ': ' . $message;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceList $priceList
     */
    public function setPriceListInfo(PriceList $priceList): void
    {
        $this->priceListId = $priceList->getId();
        $this->priceListName = $priceList->getName();
    }

    /**
     * @return int
     */
    public function getPriceListId(): int
    {
        return $this->priceListId;
    }

    /**
     * @return string
     */
    public function getPriceListName(): string
    {
        return $this->priceListName;
    }
}
