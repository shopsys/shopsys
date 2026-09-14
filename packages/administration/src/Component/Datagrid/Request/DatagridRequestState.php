<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Request;

use Symfony\Component\HttpFoundation\Request;

/**
 * Everything the request says about one datagrid, read once by `DatagridFactory` and handed to the datagrid —
 * the features narrowing the datagrid take their part from here and never touch the request themselves.
 * Only the raw query is kept; `DatagridFormFactory` turns it into forms once the datagrid knows its fields.
 *
 * The state is carried by GET forms named after the datagrid, so that two datagrids on one page never
 * clash and the pager keeps the state in its links (the framework `Grid` merges the whole query string).
 * Page, order and limit stay with the `Grid`, which reads them from its own `g[<gridId>]` namespace.
 */
final readonly class DatagridRequestState
{
    private const string QUICK_SEARCH_FORM_SUFFIX = '_search';

    /**
     * @param string $gridId The ID of the datagrid the state belongs to, its forms are named after it
     * @param array<string, mixed>|null $quickSearchQuery The query of the quick search form, null when the request does not carry it
     * @param bool $inRequest False when the datagrid is built outside a request (a command, a test) — no form is built then
     */
    public function __construct(
        public string $gridId,
        public ?array $quickSearchQuery = null,
        public bool $inRequest = true,
    ) {
    }

    public static function fromRequest(Request $request, string $gridId): self
    {
        return new self(
            $gridId,
            self::readFormQuery($request, self::createFormName($gridId, self::QUICK_SEARCH_FORM_SUFFIX)),
        );
    }

    public static function outsideRequest(string $gridId): self
    {
        return new self($gridId, inRequest: false);
    }

    public function getQuickSearchFormName(): string
    {
        return self::createFormName($this->gridId, self::QUICK_SEARCH_FORM_SUFFIX);
    }

    /**
     * A form name allows only letters, digits, underscores, hyphens and colons; the ID of a datagrid is free text.
     */
    private static function createFormName(string $gridId, string $suffix): string
    {
        return preg_replace('/[^A-Za-z0-9_]/', '_', $gridId) . $suffix;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function readFormQuery(Request $request, string $formName): ?array
    {
        return $request->query->has($formName) ? $request->query->all($formName) : null;
    }
}
