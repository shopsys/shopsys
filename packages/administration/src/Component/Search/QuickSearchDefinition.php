<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Search;

use Closure;

final class QuickSearchDefinition
{
    private ?Closure $queryCallback = null;

    /**
     * @param string[] $fields Field paths in dot notation (e.g. "catnum", "items.catnum", "translations.name")
     */
    public function __construct(
        private readonly array $fields,
        private readonly ?string $placeholder,
        private readonly ?string $infoMessage,
    ) {
    }

    /**
     * Replaces the search condition generated from the configured fields with custom logic.
     *
     * @param \Closure(\Doctrine\ORM\QueryBuilder, string): void $queryCallback Receives the list query builder (root alias "o") and the searched text
     */
    public function queryCallback(Closure $queryCallback): self
    {
        $this->queryCallback = $queryCallback;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    public function getInfoMessage(): ?string
    {
        return $this->infoMessage;
    }

    public function getQueryCallback(): ?Closure
    {
        return $this->queryCallback;
    }
}
