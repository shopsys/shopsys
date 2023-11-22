<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations\DataModifiers;

class CountryDataModifierVersion20190121094400
{
    /**
     * @var array<int, int>
     */
    private array $tmpIds;

    /**
     * @param mixed[] $data
     */
    public function __construct(private readonly array $data)
    {
        $this->tmpIds = [];
    }

    /**
     * @return mixed[]
     */
    public function getGroupedByCode(): array
    {
        $tmp = [];

        foreach ($this->data as $row) {
            $tmp[$row['code']][] = $row;
        }

        return $tmp;
    }

    /**
     * @return mixed[]
     */
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

    /**
     * @return int[]|string[]
     */
    public function getAllCodes(): array
    {
        return array_keys($this->getNewIdCodePair());
    }

    /**
     * @return mixed[]
     */
    public function getAllIds(): array
    {
        $tmp = [];

        foreach ($this->data as $row) {
            $tmp[$row['id']] = $row['id'];
        }

        return $tmp;
    }

    /**
     * @param int $oldId
     * @return int
     */
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

    /**
     * @param int $domainId
     * @param string $countryCode
     * @return bool
     */
    private function codeExistsForDomain(int $domainId, string $countryCode): bool
    {
        foreach ($this->data as $row) {
            if ($row['code'] === $countryCode && $row['domain_id'] === $domainId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param int $domainId
     * @param string $countryCode
     * @return array<'country_id'|'domain_id'|'enabled'|'priority', mixed>
     */
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

    /**
     * @param int $domainId
     * @param string $countryCode
     * @return array<'name'|'translatable_id', mixed>
     */
    public function getTranslatableDataForCountry(int $domainId, string $countryCode): array
    {
        $codeIdPairs = $this->getNewIdCodePair();

        return [
            'translatable_id' => $codeIdPairs[$countryCode],
            'name' => $this->getNameForCountryAndDomain($domainId, $countryCode),
        ];
    }

    /**
     * @param int $domainId
     * @param string $countryCode
     * @return string
     */
    private function getNameForCountryAndDomain(int $domainId, string $countryCode): string
    {
        foreach ($this->data as $row) {
            if ($row['code'] === $countryCode && $row['domain_id'] === $domainId) {
                return $row['name'];
            }
        }

        return $countryCode;
    }

    /**
     * @return mixed[]
     */
    public function getObsoleteCountryIds(): array
    {
        $obsoleteIds = [];

        foreach ($this->data as $row) {
            $obsoleteIds[] = $row['id'];
        }

        $usedIds = array_values($this->getNewIdCodePair());

        return array_values(array_diff($obsoleteIds, $usedIds));
    }

    /**
     * @param mixed[] $data
     * @return mixed[]
     */
    private function groupDataIntoDomains(array $data): array
    {
        $tmp = [];

        foreach ($data as $row) {
            $tmp[$row['domain_id']][] = $row;
        }

        return $tmp;
    }
}
