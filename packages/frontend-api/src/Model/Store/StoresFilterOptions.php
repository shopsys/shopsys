<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Store;

class StoresFilterOptions
{
    /**
     * @param string|null $searchText
     * @param array{latitude: string, longitude: string}|null $coordinates
     */
    public function __construct(
        protected readonly ?string $searchText,
        protected readonly ?array $coordinates = null,
    ) {
    }

    /**
     * @return string|null
     */
    public function getSearchText(): ?string
    {
        return $this->searchText;
    }

    /**
     * @return array{latitude: string, longitude: string}|null
     */
    public function getCoordinates(): ?array
    {
        return $this->coordinates;
    }
}
