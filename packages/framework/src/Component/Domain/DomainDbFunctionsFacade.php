<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain;

use Doctrine\ORM\EntityManagerInterface;

class DomainDbFunctionsFacade
{
    public function __construct(protected readonly EntityManagerInterface $em, protected readonly Domain $domain)
    {
    }

    public function createDomainDbFunctions(): void
    {
        $this->createDomainIdsByLocaleFunction();
        $this->createLocaleByDomainIdFunction();
    }

    protected function createDomainIdsByLocaleFunction(): void
    {
        $connection = $this->em->getConnection();
        $databasePlatform = $connection->getDatabasePlatform();
        $domainsIdsByLocale = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domainConfig) {
            $domainsIdsByLocale[$domainConfig->getLocale()][] = $domainConfig->getId();
        }

        $domainIdsByLocaleSqlClauses = [];

        foreach ($domainsIdsByLocale as $locale => $domainIds) {
            $sql = 'WHEN locale = ' . $databasePlatform->quoteStringLiteral($locale) . ' THEN ';

            foreach ($domainIds as $domainId) {
                $sql .= ' RETURN NEXT ' . $domainId . ';';
            }
            $domainIdsByLocaleSqlClauses[] = $sql;
        }

        $connection->executeStatement(
            'CREATE OR REPLACE FUNCTION get_domain_ids_by_locale(locale text) RETURNS SETOF integer AS $$
            BEGIN
                CASE
                    ' . implode("\n", $domainIdsByLocaleSqlClauses) . '
                    ELSE RETURN;
                END CASE;
            END
            $$ LANGUAGE plpgsql IMMUTABLE;',
        );
    }

    protected function createLocaleByDomainIdFunction(): void
    {
        $connection = $this->em->getConnection();
        $databasePlatform = $connection->getDatabasePlatform();
        $localeByDomainIdSqlClauses = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domainConfig) {
            $localeByDomainIdSqlClauses[] =
                'WHEN domain_id = ' . $domainConfig->getId()
                . ' THEN RETURN ' . $databasePlatform->quoteStringLiteral($domainConfig->getLocale()) . ';';
        }

        $connection->executeStatement(
            'CREATE OR REPLACE FUNCTION get_domain_locale(domain_id integer) RETURNS text AS $$
            BEGIN
                CASE
                    ' . implode("\n", $localeByDomainIdSqlClauses) . '
                    ELSE RAISE EXCEPTION \'Domain with ID % does not exists\', domain_id;
                END CASE;
            END
            $$ LANGUAGE plpgsql IMMUTABLE;',
        );
    }
}
