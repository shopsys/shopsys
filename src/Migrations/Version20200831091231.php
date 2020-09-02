<?php

declare(strict_types=1);

namespace App\Migrations;

use App\Model\CategorySeo\ChoseCategorySeoMixCombination;
use Doctrine\DBAL\Schema\Schema;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200831091231 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $readyCategorySeoMixesData = $this->sql('SELECT id, category_id, flag_id, domain_id 
                                                        FROM ready_category_seo_mixes 
                                                        WHERE ordering IS NULL')->fetchAll();

        foreach ($readyCategorySeoMixesData as $categorySeoMixData) {
            $readyCategorySeoMixParamValues = $this->sql(
                'SELECT parameter_id, parameter_value_id 
                        FROM ready_category_seo_mix_parameter_parameter_values 
                        WHERE ready_category_seo_mix_id = :readyCategorySeoMixId
                        ORDER BY parameter_id, parameter_value_id',
                [
                    'readyCategorySeoMixId' => $categorySeoMixData['id']
                ]
            )->fetchAll();

            $tmpReadyCategorySeoMixParamValues = [];
            foreach ($readyCategorySeoMixParamValues as $categorySeoMixParamValue) {
                $tmpReadyCategorySeoMixParamValues[$categorySeoMixParamValue['parameter_id']] = $categorySeoMixParamValue['parameter_value_id'];
            }

            $combinationArray = ChoseCategorySeoMixCombination::getChoseCategorySeoMixCombinationArray(
                $categorySeoMixData['domain_id'],
                $categorySeoMixData['category_id'],
                $categorySeoMixData['flag_id'],
                ProductListOrderingConfig::ORDER_BY_PRIORITY,
                $tmpReadyCategorySeoMixParamValues
            );
            $combinationJson = json_encode($combinationArray);

            $duplicateSeoMix = $this->sql(
                'SELECT id 
                        FROM ready_category_seo_mixes 
                        WHERE chose_category_seo_mix_combination_json = :combinationJson',
                [
                    'combinationJson' => $combinationJson
                ]
            )->fetchAll();

            if (count($duplicateSeoMix) === 0) {
                $this->sql(
                    'UPDATE ready_category_seo_mixes 
                            SET chose_category_seo_mix_combination_json = :combinationJson, ordering = :ordering
                            WHERE id = :id',
                    [
                        'id' => $categorySeoMixData['id'],
                        'combinationJson' => $combinationJson,
                        'ordering' => ProductListOrderingConfig::ORDER_BY_PRIORITY,
                    ]
                );
            } else {
                $this->sql(
                    'DELETE FROM ready_category_seo_mix_parameter_parameter_values WHERE ready_category_seo_mix_id = :readyCategorySeoMixId',
                    [
                    'readyCategorySeoMixId' => $categorySeoMixData['id']
                ]
                );
                $this->sql(
                    'DELETE FROM ready_category_seo_mixes WHERE id = :readyCategorySeoMixId',
                    [
                    'readyCategorySeoMixId' => $categorySeoMixData['id']
                ]
                );
            }
        }

        $this->sql('ALTER TABLE ready_category_seo_mixes ALTER ordering SET NOT NULL');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
