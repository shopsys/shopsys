<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations\DataModifiers;

final class CountryDataModifierVersion20190121094400
{
    /**
     * @var array<int, int>
     */
    private array $tmpIds;

    public function __construct(private readonly array $data)
    {
        $this->tmpIds = [];
    }

    public function getNewIdCodePair(): array
    {
        $data = $this->groupDataIntoDomains($this->data);

        $tmp = [];

        foreach ($data as $domainId => $domainData) {
            foreach ($domainData as $row) {
                if ($domainId === 1 || !array_key_exists($row['code'], $tmp)) {
                    $tmp[$row['code']] = $row['id'];
                }
            }
        }

        return $tmp;
    }

    public function getAllCodes(): array
    {
        return array_keys($this->getNewIdCodePair());
    }

    public function getAllIds(): array
    {
        $tmp = [];

        foreach ($this->data as $row) {
            $tmp[$row['id']] = $row['id'];
        }

        return $tmp;
    }

    public function getNewId(int $oldId): int
    {
        if (count($this->tmpIds) === 0) {
            $this->loadIdPairs();
        }

        return $this->tmpIds[$oldId];
    }

    private function loadIdPairs(): void
    {
        $pair = $this->getNewIdCodePair();

        foreach ($this->data as $row) {
            $this->tmpIds[$row['id']] = $pair[$row['code']];
        }
    }

    private function codeExistsForDomain(int $domainId, string $countryCode): bool
    {
        foreach ($this->data as $row) {
            if ($row['code'] === $countryCode && $row['domain_id'] === $domainId) {
                return true;
            }
        }

        return false;
    }

    public function getDomainDataForCountry(int $domainId, string $countryCode): array
    {
        $codeIdPairs = $this->getNewIdCodePair();

        return [
            'country_id' => $codeIdPairs[$countryCode],
            'domain_id' => $domainId,
            'enabled' => $this->codeExistsForDomain($domainId, $countryCode),
            'priority' => 0,
        ];
    }

    public function getTranslatableDataForCountry(int $domainId, string $countryCode): array
    {
        $codeIdPairs = $this->getNewIdCodePair();

        return [
            'translatable_id' => $codeIdPairs[$countryCode],
            'name' => $this->getNameForCountryAndDomain($domainId, $countryCode),
        ];
    }

    private function getNameForCountryAndDomain(int $domainId, string $countryCode): string
    {
        foreach ($this->data as $row) {
            if ($row['code'] === $countryCode && $row['domain_id'] === $domainId) {
                return $row['name'];
            }
        }

        return $countryCode;
    }

    public function getObsoleteCountryIds(): array
    {
        $obsoleteIds = [];

        foreach ($this->data as $row) {
            $obsoleteIds[] = $row['id'];
        }

        $usedIds = array_values($this->getNewIdCodePair());

        return array_values(array_diff($obsoleteIds, $usedIds));
    }

    private function groupDataIntoDomains(array $data): array
    {
        $tmp = [];

        foreach ($data as $row) {
            $tmp[$row['domain_id']][] = $row;
        }

        return $tmp;
    }
}
