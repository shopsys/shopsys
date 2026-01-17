<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

class MoneyConvertingDataSourceDecoratorFactory
{
    /**
     * @param array<string> $moneyColumnNames
     */
    public function create(
        DataSourceInterface $innerDataSource,
        array $moneyColumnNames,
    ): MoneyConvertingDataSourceDecorator {
        return new MoneyConvertingDataSourceDecorator($innerDataSource, $moneyColumnNames);
    }
}
