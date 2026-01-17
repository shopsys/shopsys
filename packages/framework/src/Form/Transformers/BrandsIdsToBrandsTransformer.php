<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Override;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandRepository;
use Symfony\Component\Form\DataTransformerInterface;

class BrandsIdsToBrandsTransformer implements DataTransformerInterface
{
    public function __construct(protected readonly BrandRepository $brandRepository)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]|null $brands
     * @return int[]
     */
    #[Override]
    public function transform($brands): array
    {
        if (is_array($brands) === false) {
            return [];
        }

        return array_map(static fn (Brand $brand) => $brand->getId(), $brands);
    }

    /**
     * @param int[] $brandsIds
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    #[Override]
    public function reverseTransform($brandsIds): array
    {
        if (is_array($brandsIds) === false || count($brandsIds) === 0) {
            return [];
        }

        return $this->brandRepository->getBrandsByIdsPreservingInputOrder($brandsIds);
    }
}
