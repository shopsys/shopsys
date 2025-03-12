<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Test;

use Override;
use PHPUnit\Framework\Constraint\Constraint;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

final class IsPriceEqual extends Constraint
{
    private PriceExporter $exporter;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface $value
     */
    public function __construct(private readonly PriceInterface $value)
    {
        $this->exporter = new PriceExporter();
    }

    /**
     * @return string
     */
    #[Override]
    public function toString(): string
    {
        return 'is equal price to expected ' . $this->exporter->export($this->value);
    }

    /**
     * @param mixed $other
     * @return bool
     */
    #[Override]
    protected function matches($other): bool
    {
        return $other instanceof PriceInterface && $other->equals($this->value);
    }
}
