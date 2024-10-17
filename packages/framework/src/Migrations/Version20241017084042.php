<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20241017084042 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200319113341')) {
            $this->sql('
            CREATE TABLE ready_category_seo_mix_parameter_parameter_values (
                ready_category_seo_mix_id INT NOT NULL,
                parameter_id INT NOT NULL,
                parameter_value_id INT NOT NULL,
                PRIMARY KEY(
                    ready_category_seo_mix_id, parameter_id,
                    parameter_value_id
                )
            )');
            $this->sql('
            CREATE INDEX IDX_428D0DF07C7FCEDE ON ready_category_seo_mix_parameter_parameter_values (ready_category_seo_mix_id)');
            $this->sql('
            CREATE INDEX IDX_428D0DF07C56DBD6 ON ready_category_seo_mix_parameter_parameter_values (parameter_id)');
            $this->sql('
            CREATE INDEX IDX_428D0DF01452663E ON ready_category_seo_mix_parameter_parameter_values (parameter_value_id)');
            $this->sql('
            CREATE TABLE ready_category_seo_mixes (
                id SERIAL NOT NULL,
                category_id INT NOT NULL,
                flag_id INT DEFAULT NULL,
                domain_id INT NOT NULL,
                chose_category_seo_mix_combination_json TEXT NOT NULL,
                ordering VARCHAR(255) DEFAULT NULL,
                h1 TEXT NOT NULL,
                description TEXT DEFAULT NULL,
                title TEXT DEFAULT NULL,
                meta_description TEXT DEFAULT NULL,
                PRIMARY KEY(id)
            )');
            $this->sql('CREATE INDEX IDX_74803E8F12469DE2 ON ready_category_seo_mixes (category_id)');
            $this->sql('CREATE INDEX IDX_74803E8F919FE4E5 ON ready_category_seo_mixes (flag_id)');
            $this->sql('
            CREATE UNIQUE INDEX chose_category_seo_mix_combination_json ON ready_category_seo_mixes (
                chose_category_seo_mix_combination_json
            )');
            $this->sql('
            ALTER TABLE
                ready_category_seo_mix_parameter_parameter_values
            ADD
                CONSTRAINT FK_428D0DF07C7FCEDE FOREIGN KEY (ready_category_seo_mix_id) REFERENCES ready_category_seo_mixes (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('
            ALTER TABLE
                ready_category_seo_mix_parameter_parameter_values
            ADD
                CONSTRAINT FK_428D0DF07C56DBD6 FOREIGN KEY (parameter_id) REFERENCES parameters (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('
            ALTER TABLE
                ready_category_seo_mix_parameter_parameter_values
            ADD
                CONSTRAINT FK_428D0DF01452663E FOREIGN KEY (parameter_value_id) REFERENCES parameter_values (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('
            ALTER TABLE
                ready_category_seo_mixes
            ADD
                CONSTRAINT FK_74803E8F12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('
            ALTER TABLE
                ready_category_seo_mixes
            ADD
                CONSTRAINT FK_74803E8F919FE4E5 FOREIGN KEY (flag_id) REFERENCES flags (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200520151921')) {
            $this->sql('ALTER TABLE ready_category_seo_mixes ADD short_description TEXT DEFAULT NULL');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200831091231')) {
            $this->sql('ALTER TABLE ready_category_seo_mixes ALTER ordering SET NOT NULL');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20201106174818')) {
            $this->sql('ALTER TABLE ready_category_seo_mixes ADD show_in_category BOOLEAN NOT NULL DEFAULT false');
            $this->sql('ALTER TABLE ready_category_seo_mixes ALTER show_in_category DROP DEFAULT');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20211014060332')) {
            $this->sql('ALTER TABLE ready_category_seo_mixes DROP CONSTRAINT FK_74803E8F12469DE2');
            $this->sql('
            ALTER TABLE
                ready_category_seo_mixes
            ADD
                CONSTRAINT FK_74803E8F12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20220628185551')) {
            $this->sql('ALTER TABLE ready_category_seo_mixes ADD uuid UUID DEFAULT NULL');
            $this->sql('UPDATE ready_category_seo_mixes SET uuid = uuid_generate_v4()');
            $this->sql('ALTER TABLE ready_category_seo_mixes ALTER uuid SET NOT NULL');
            $this->sql('CREATE UNIQUE INDEX UNIQ_74803E8FD17F50A6 ON ready_category_seo_mixes (uuid)');
        }
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
