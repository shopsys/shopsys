<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Test;

use Ramsey\Uuid\Uuid;

class SearchInputTestUtils
{
    /**
     * @param string $search
     * @param bool $isAutocomplete
     * @param string|null $userIdentifier
     * @return array
     */
    public static function createSearchInputQueryVariables(
        string $search,
        bool $isAutocomplete = false,
        ?string $userIdentifier = null,
    ): array {
        return self::createSearchInputArray($search, $isAutocomplete, $userIdentifier);
    }

    /**
     * @param \Tests\FrontendApiBundle\Test\ReferenceDataAccessor $search
     * @param bool $isAutocomplete
     * @param string|null $userIdentifier
     * @return array
     */
    public static function createSearchInputQueryVariablesByReference(
        ReferenceDataAccessor $search,
        bool $isAutocomplete = false,
        ?string $userIdentifier = null,
    ) {
        return self::createSearchInputArray($search, $isAutocomplete, $userIdentifier);
    }

    /**
     * @param string|\Tests\FrontendApiBundle\Test\ReferenceDataAccessor $search
     * @param bool $isAutocomplete
     * @param string|null $userIdentifier
     * @return array
     */
    private static function createSearchInputArray(
        string|ReferenceDataAccessor $search,
        bool $isAutocomplete,
        ?string $userIdentifier,
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
