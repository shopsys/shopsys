<?php

declare(strict_types=1);

namespace App\Model\Product\Package;

class ProductPackagesCollection
{
    /**
     * @var \App\Model\Product\Package\NormalizedProductPackage[]
     */
    private array $normalizedProductPackages;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param \App\Model\Product\Package\ProductPackage[] $productPackages
     */
    public function __construct(array $quantifiedProducts, array $productPackages)
    {
        $quantitiesByProductId = [];
        foreach ($quantifiedProducts as $quantifiedProduct) {
            $quantitiesByProductId[$quantifiedProduct->getProduct()->getId()] = $quantifiedProduct->getQuantity();
        }

        $this->normalizedProductPackages = [];
        foreach ($productPackages as $productPackage) {
            $quantity = $quantitiesByProductId[$productPackage->getProduct()->getId()];

            for ($i = 0; $i < $quantity; $i++) {
                $this->normalizedProductPackages[] = new NormalizedProductPackage(
                    $productPackage->getWeight(),
                    $productPackage->getWidth(),
                    $productPackage->getHeight(),
                    $productPackage->getLength()
                );
            }
        }
    }

    /**
     * @return int
     */
    public function getTotalPackagesCount(): int
    {
        return count($this->normalizedProductPackages);
    }

    /**
     * @return float
     */
    public function getWeightSum(): float
    {
        $sum = 0.0;
        foreach ($this->normalizedProductPackages as $normalizedProductPackage) {
            $sum += $normalizedProductPackage->getWeight();
        }

        return $sum;
    }

    /**
     * @return int
     */
    public function getTotalGirth(): int
    {
        $allDimensions = [];
        foreach ($this->normalizedProductPackages as $normalizedProductPackage) {
            $allDimensions[] = $normalizedProductPackage->getDimension1();
            $allDimensions[] = $normalizedProductPackage->getDimension2();
            $allDimensions[] = $normalizedProductPackage->getDimension3();
        }
        sort($allDimensions);
        $sortedAllDimensions = array_reverse($allDimensions);

        $topDimension = $sortedAllDimensions[0];
        $secondTopDimension = $sortedAllDimensions[1];
        $dimension3Sum = $this->getDimension3Sum();

        return $topDimension + 2 * $secondTopDimension + 2 * $dimension3Sum;
    }

    /**
     * @return int
     */
    public function getTopDimension1(): int
    {
        $top = 0;
        foreach ($this->normalizedProductPackages as $normalizedProductPackage) {
            if ($normalizedProductPackage->getDimension1() > $top) {
                $top = $normalizedProductPackage->getDimension1();
            }
        }

        return $top;
    }

    /**
     * @return int
     */
    public function getTopDimension2(): int
    {
        $top = 0;
        foreach ($this->normalizedProductPackages as $normalizedProductPackage) {
            if ($normalizedProductPackage->getDimension2() > $top) {
                $top = $normalizedProductPackage->getDimension2();
            }
        }

        return $top;
    }

    /**
     * @return int
     */
    public function getDimension3Sum(): int
    {
        $sum = 0;
        foreach ($this->normalizedProductPackages as $normalizedProductPackage) {
            $sum += $normalizedProductPackage->getDimension3();
        }
        return $sum;
    }
}
