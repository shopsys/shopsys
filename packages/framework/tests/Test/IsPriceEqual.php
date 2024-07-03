<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Test;

use PHPUnit\Framework\Constraint\Constraint;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

final class IsPriceEqual extends Constraint
{
    private PriceExporter $exporter;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $value
     */
    public function __construct(private readonly Price $value)
    {
        $this->exporter = new PriceExporter();
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return 'is equal price to expected ' . $this->exporter->export($this->value);
    }

    /**
     * @param mixed $other
     * @return bool
     */
    protected function matches($other): bool
    {
        return $other instanceof Price && $other->equals($this->value);
    }
}
