<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Test;

use Ramsey\Uuid\Uuid;

class SearchInputTestUtils
{
    /**
     * @param string|\Tests\FrontendApiBundle\Test\ReferenceDataAccessor $search
     * @param bool $isAutocomplete
     * @param string|null $userIdentifier
     * @return array
     */
    public static function createSearchInputQueryVariables(
        string|ReferenceDataAccessor $search,
        bool $isAutocomplete = false,
        ?string $userIdentifier = null,
    ): array {
        return [
            'searchInput' => [
                'search' => $search,
                'isAutocomplete' => $isAutocomplete,
                'userIdentifier' => $userIdentifier ?: Uuid::uuid4(),
            ],
        ];
    }
}
