<?php

declare(strict_types=1);

namespace App\Model\Product\Package;

class NormalizedProductPackage
{
    /**
     * @var float
     */
    private float $weight;

    /**
     * @var int
     */
    private int $dimension1;

    /**
     * @var int
     */
    private int $dimension2;

    /**
     * @var int
     */
    private int $dimension3;

    /**
     * @param float $weight
     * @param int $width
     * @param int $height
     * @param int $length
     */
    public function __construct(float $weight, int $width, int $height, int $length)
    {
        $this->weight = $weight;

        $dimensions = [$width, $height, $length];
        sort($dimensions, SORT_NUMERIC);

        $this->dimension1 = $dimensions[2];
        $this->dimension2 = $dimensions[1];
        $this->dimension3 = $dimensions[0];
    }

    /**
     * @return float
     */
    public function getWeight(): float
    {
        return $this->weight;
    }

    /**
     * @return int
     */
    public function getDimension1(): int
    {
        return $this->dimension1;
    }

    /**
     * @return int
     */
    public function getDimension2(): int
    {
        return $this->dimension2;
    }

    /**
     * @return int
     */
    public function getDimension3(): int
    {
        return $this->dimension3;
    }
}
