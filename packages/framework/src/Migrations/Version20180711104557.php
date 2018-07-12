<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20180711104557 extends AbstractMigration
{
    use MultidomainMigrationTrait;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema)
    {
        $countries = $this
            ->sql('SELECT id, name, domain_id, code FROM countries')
            ->fetchAll();


        $allDomainIds = $this->getAllDomainIds();
        $locales = [];
        foreach($allDomainIds as $domainId) {
            $locales[$domainId] =  $this->getDomainLocale($domainId);
        }

        $transformator = new Version20180711104557CountryTransformator();
        var_dump(
            $countries,
            $transformator->getMainCountries($countries),
            $transformator->getTranslations($countries, $locales),
            $transformator->getCountryDomains($countries, $allDomainIds),
            $transformator->getOldToNewIdsMap($countries)
        );

        throw new \Exception('x');

        $this->sql('
            CREATE TABLE country_translations (
                id SERIAL NOT NULL,
                translatable_id INT NOT NULL,
                name VARCHAR(255) DEFAULT NULL,
                locale VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_CA1456952C2AC5D3 ON country_translations (translatable_id)');
        $this->sql('
            CREATE UNIQUE INDEX country_translations_uniq_trans ON country_translations (translatable_id, locale)');
        $this->sql('
            ALTER TABLE
                country_translations
            ADD
                CONSTRAINT FK_CA1456952C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES countries (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
