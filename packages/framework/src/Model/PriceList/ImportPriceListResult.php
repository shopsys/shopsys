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

    public function addError(int $line, string $error): void
    {
        $this->errors[] = $this->formatMessage($line, $error);
    }

    public function addWarning(int $line, string $warning): void
    {
        $this->warnings[] = $this->formatMessage($line, $warning);
    }

    public function addGeneralWarning(string $warning): void
    {
        $this->warnings[] = $warning;
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

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function increaseSuccessfulCount(): void
    {
        $this->importedCount++;
    }

    public function decreaseSuccessfulCount(): void
    {
        $this->importedCount--;
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    public function hasWarnings(): bool
    {
        return count($this->warnings) > 0;
    }

    public function formatMessage(int $line, string $message): string
    {
        return t('Line:') . '#' . $line . ': ' . $message;
    }

    public function setPriceListInfo(PriceList $priceList): void
    {
        $this->priceListId = $priceList->getId();
        $this->priceListName = $priceList->getName();
    }

    public function getPriceListId(): int
    {
        return $this->priceListId;
    }

    public function getPriceListName(): string
    {
        return $this->priceListName;
    }
}
