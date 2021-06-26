<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Products\DataMapper;

use Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductArrayFieldMapper as BaseProductArrayFieldMapper;

class ProductArrayFieldMapper extends BaseProductArrayFieldMapper
{
    /**
     * @param array $data
     * @return string
     */
    public function getFullname(array $data): string
    {
        return trim(
            $data['name_prefix']
            . ' '
            . $data['name']
            . ' '
            . $data['name_sufix']
        );
    }

    /**
     * @param array $data
     * @return bool
     */
    public function isInSale(array $data): bool
    {
        return $data['is_in_sale'];
    }

    /**
     * @param array $data
     * @return bool
     */
    public function hasPreorder(array $data): bool
    {
        return $data['has_preorder'];
    }

    /**
     * @param array $data
     * @return bool
     */
    public function hasSaleExclusion(array $data): bool
    {
        return $data['is_sale_exclusion'];
    }

    /**
     * @param array $data
     * @return string|null
     */
    public function getNamePrefix(array $data): ?string
    {
        return $data['name_prefix'];
    }

    /**
     * @param array $data
     * @return string|null
     */
    public function getNameSuffix(array $data): ?string
    {
        return $data['name_sufix'];
    }

    /**
     * @param array $data
     * @return string|null
     */
    public function getPartNumber(array $data): ?string
    {
        return $data['partno'];
    }

    /**
     * @param array $data
     * @return string
     */
    public function getCatalogNumber(array $data): string
    {
        return $data['catnum'];
    }

    /**
     * @param array $data
     * @return array
     */
    public function getAvailability(array $data): array
    {
        return [
            'name' => $data['availability'],
            'status' => $data['availability_status'],
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    public function getFiles(array $data): array
    {
        return array_map(
            static fn ($fileData) => [
                'anchorText' => $fileData['anchor_text'],
                'url' => $fileData['url'],
            ],
            $data['files']
        );
    }
}
