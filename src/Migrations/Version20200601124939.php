<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200601124939 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $data = $this->sql('SELECT id, chose_category_seo_mix_combination_json FROM ready_category_seo_mixes');
        foreach ($data->fetchAll() as $row) {
            $seoMixCombination = \GuzzleHttp\json_decode($row['chose_category_seo_mix_combination_json'], true);
            ksort($seoMixCombination['parameterValueIdsByParameterIds']);
            $this->sql(
                'UPDATE ready_category_seo_mixes SET chose_category_seo_mix_combination_json = :json WHERE id = :id',
                [
                    'json' => \GuzzleHttp\json_encode($seoMixCombination),
                    'id' => $row['id'],
                ]
            );
        }
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
