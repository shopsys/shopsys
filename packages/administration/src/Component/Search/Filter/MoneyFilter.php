<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Search\Filter;

use Override;

/**
 * Numeric comparison filter for monetary amounts (two decimal places by default).
 * The comparison is on the raw column — it is not currency-aware.
 */
final class MoneyFilter extends NumberFilter
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormOptions(): array
    {
        return $this->valueFormOptions + ['scale' => 2];
    }
}
