<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store;

class StoresFilterOptions
{
    /**
     * @param string|null $searchText
     */
    public function __construct(
        public readonly ?string $searchText,
    ) {
    }

    /**
     * @return string|null
     */
    public function getSearchText(): ?string
    {
        return $this->searchText;
    }
}
