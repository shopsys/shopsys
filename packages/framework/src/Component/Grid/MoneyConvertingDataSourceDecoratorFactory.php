<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

class MoneyConvertingDataSourceDecoratorFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface $innerDataSource
     * @param array<string> $moneyColumnNames
     * @return \Shopsys\FrameworkBundle\Component\Grid\MoneyConvertingDataSourceDecorator
     */
    public function create(
        DataSourceInterface $innerDataSource,
        array $moneyColumnNames,
    ): MoneyConvertingDataSourceDecorator {
        return new MoneyConvertingDataSourceDecorator($innerDataSource, $moneyColumnNames);
    }
}
