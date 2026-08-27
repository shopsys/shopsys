<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Search;

final class SearchConfig
{
    public const string QUICK_SEARCH_QUERY_PARAMETER = 'q';

    private ?QuickSearchDefinition $quickSearchDefinition = null;

    /**
     * Enables the quick search box on the list page, searching the given fields with a case- and accent-insensitive match.
     *
     * @param string[] $fields Field paths in dot notation (e.g. "catnum", "items.catnum", "translations.name")
     */
    public function enableQuickSearch(
        array $fields = [],
        ?string $placeholder = null,
        ?string $infoMessage = null,
    ): QuickSearchDefinition {
        $this->quickSearchDefinition = new QuickSearchDefinition($fields, $placeholder, $infoMessage);

        return $this->quickSearchDefinition;
    }

    public function isQuickSearchEnabled(): bool
    {
        return $this->quickSearchDefinition !== null;
    }

    public function getQuickSearchDefinition(): ?QuickSearchDefinition
    {
        return $this->quickSearchDefinition;
    }
}
