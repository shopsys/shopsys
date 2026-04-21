<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20260312111907 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->migrateCountryAwarePhoneNumberColumn('orders', 'telephone', 'country_id');
        $this->migrateCountryAwarePhoneNumberColumn('orders', 'delivery_telephone', 'delivery_country_id');
        $this->migrateCountryAwarePhoneNumberColumn('delivery_addresses', 'telephone', 'country_id');
        $this->migrateCountryAwarePhoneNumberColumn(
            'customer_users',
            'telephone',
            '(SELECT ba.country_id FROM billing_addresses ba WHERE ba.customer_id = customer_users.customer_id LIMIT 1)',
        );

        $this->migrateCountryAwarePhoneNumberColumn('complaints', 'delivery_telephone', 'delivery_country_id');

        $this->migratePhoneNumberColumn('sales_representatives', 'telephone', null);

        $this->migratePhoneNumberColumn('inquiries', 'telephone', null);

        $this->migratePhoneNumberColumn('withdrawal_requests', 'telephone', null);
    }

    private function migrateCountryAwarePhoneNumberColumn(
        string $tableName,
        string $columnNamePrefix,
        string $countryIdExpression,
    ): void {
        $countries = $this->sqlQuery('SELECT id, code FROM countries')->fetchAllAssociative();

        foreach ($countries as $country) {
            $this->migratePhoneNumberColumn(
                $tableName,
                $columnNamePrefix,
                $country['code'],
                filterSql: $countryIdExpression . ' = :countryId',
                filterParams: ['countryId' => $country['id']],
            );
        }

        // Handle remaining rows without a matching country - no default prefix
        $this->migratePhoneNumberColumn($tableName, $columnNamePrefix, null);
    }

    /**
     * @param array<string, mixed> $filterParams
     */
    private function migratePhoneNumberColumn(
        string $tableName,
        string $columnNamePrefix,
        ?string $defaultCountryCode,
        ?string $filterSql = null,
        array $filterParams = [],
    ): void {
        $phoneUtil = PhoneNumberUtil::getInstance();
        $defaultPhonePrefix = null;

        if ($defaultCountryCode !== null) {
            $dialCode = $phoneUtil->getCountryCodeForRegion($defaultCountryCode);

            if ($dialCode !== 0) {
                $defaultPhonePrefix = '+' . $dialCode;
            }
        }

        $columnNumberName = $columnNamePrefix . '_number';
        $columnPrefixName = $columnNamePrefix . '_prefix';

        $sql = 'SELECT id, ' . $columnNumberName .
            ' FROM ' . $tableName .
            ' WHERE ' . $columnNumberName . ' IS NOT NULL AND ' . $columnNumberName . ' != \'\' AND (' . $columnPrefixName . ' IS NULL OR ' . $columnPrefixName . ' = \'\')';
        $params = [];

        if ($filterSql !== null) {
            $sql .= ' AND ' . $filterSql;
            $params = array_merge($params, $filterParams);
        }

        $selectQuery = $this->sqlQuery($sql, $params);

        while ($row = $selectQuery->fetchAssociative()) {
            $this->migratePhoneNumber(
                (int)$row['id'],
                $tableName,
                $columnNamePrefix,
                $row[$columnNumberName],
                $defaultCountryCode,
                $defaultPhonePrefix,
            );
        }
    }

    private function migratePhoneNumber(
        int $entityId,
        string $tableName,
        string $columnNamePrefix,
        ?string $telephoneNumber,
        ?string $defaultCountryCode,
        ?string $defaultPrefix,
    ): void {
        $telephone = $this->parsePhoneNumber($telephoneNumber, $defaultCountryCode, $defaultPrefix);

        $this->sql(
            'UPDATE ' . $tableName . ' SET
                ' . $columnNamePrefix . '_prefix = :telephonePrefix,
                ' . $columnNamePrefix . '_prefix_country_code = :telephoneCountryCode,
                ' . $columnNamePrefix . '_number = :telephoneNumber
             WHERE id = :id',
            [
                'telephonePrefix' => $telephone['prefix'],
                'telephoneCountryCode' => $telephone['countryCode'],
                'telephoneNumber' => $telephone['number'],
                'id' => $entityId,
            ],
        );
    }

    /**
     * @return array{prefix: string|null, countryCode: string|null, number: string|null}
     */
    private function parsePhoneNumber(
        ?string $fullNumber,
        ?string $defaultCountryCode,
        ?string $defaultPrefix,
    ): array {
        $phoneUtil = PhoneNumberUtil::getInstance();

        if ($fullNumber === null || $fullNumber === '') {
            return ['prefix' => null, 'countryCode' => null, 'number' => null];
        }

        try {
            $phoneNumber = $phoneUtil->parse($fullNumber, $defaultCountryCode);
            $prefix = '+' . $phoneNumber->getCountryCode();
            $countryCode = $phoneUtil->getRegionCodeForCountryCode($phoneNumber->getCountryCode());
            $number = (string)$phoneNumber->getNationalNumber();

            return ['prefix' => $prefix, 'countryCode' => $countryCode, 'number' => $number];
        } catch (NumberParseException) {
            if (str_starts_with($fullNumber, '+')) {
                // Starts with + but cannot be parsed - likely invalid or non-E.164 number, keep as is without prefix
                return ['prefix' => null, 'countryCode' => null, 'number' => $fullNumber];
            }

            // Cannot parse - keep original number and apply default prefix
            return ['prefix' => $defaultPrefix, 'countryCode' => $defaultCountryCode, 'number' => $fullNumber];
        }
    }
}
