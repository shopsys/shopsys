<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200820090843 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $parameterValues = $this->sql('SELECT id, text, locale, rgb_hex FROM parameter_values WHERE rgb_hex IS NOT NULL')->fetchAll();

        foreach ($parameterValues as $parameterValue) {
            $parameterValuesWithoutRgbHex = $this->sql('SELECT id, text, locale, rgb_hex FROM parameter_values WHERE rgb_hex IS NULL AND text = :parameterValueText AND locale = :parameterValueLocale',
                    [
                        'parameterValueText' => $parameterValue['text'],
                        'parameterValueLocale' => $parameterValue['locale'],
                    ]
            )->fetchAll();

            foreach ($parameterValuesWithoutRgbHex as $parameterValueWithoutRgbHex) {
                $productParameterValues = $this->sql('SELECT product_id, parameter_id, value_id FROM product_parameter_values WHERE value_id = :parameterValueWithoutRgbHexId',
                        ['parameterValueWithoutRgbHexId' => $parameterValueWithoutRgbHex['id']]
                )->fetchAll();

                foreach ($productParameterValues as $productParameterValue) {
                    $this->sql('DELETE FROM product_parameter_values WHERE product_id = :productId AND parameter_id = :parameterId AND value_id = :oldValueId',
                               [
                                   'productId' => $productParameterValue['product_id'],
                                   'parameterId' => $productParameterValue['parameter_id'],
                                   'oldValueId' => $productParameterValue['value_id'],
                               ]
                    );
                    $this->sql('INSERT INTO product_parameter_values (product_id, parameter_id, value_id) VALUES (:productId, :parameterId, :newValueId)',
                               [
                                   'productId' => $productParameterValue['product_id'],
                                   'parameterId' => $productParameterValue['parameter_id'],
                                   'newValueId' => $parameterValue['id'],
                               ]
                    );
                }

                $this->sql('DELETE FROM parameter_values WHERE id = :parameterValueId',
                           ['parameterValueId' => $parameterValueWithoutRgbHex['id']]
                );
            }
        }
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
