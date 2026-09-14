<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Request;

use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Symfony\Component\Form\FormInterface;

/**
 * Everything the request says about one datagrid, read once by `DatagridRequestStateResolver` and handed
 * to the datagrid — the features narrowing the datagrid take their part from here and never touch the
 * request themselves.
 *
 * The state is carried by GET forms named after the datagrid, so that two datagrids on one page never
 * clash and the pager keeps the state in its links (the framework `Grid` merges the whole query string).
 * Page, order and limit stay with the `Grid`, which reads them from its own `g[<gridId>]` namespace.
 */
final readonly class DatagridRequestState
{
    private const string QUICK_SEARCH_FORM_SUFFIX = '_search';

    /**
     * @param \Symfony\Component\Form\FormInterface|null $quickSearchForm Null when the datagrid is built outside a request (a command, a test)
     */
    public function __construct(
        public ?FormInterface $quickSearchForm = null,
    ) {
    }

    /**
     * A form name allows only letters, digits, underscores, hyphens and colons; the ID of a datagrid is free text.
     */
    public static function createQuickSearchFormName(string $gridId): string
    {
        return self::toFormName($gridId) . self::QUICK_SEARCH_FORM_SUFFIX;
    }

    private static function toFormName(string $gridId): string
    {
        return preg_replace('/[^A-Za-z0-9_]/', '_', $gridId);
    }

    /**
     * The searched text, null when nothing was searched for.
     */
    public function getSearchTerm(): ?string
    {
        $data = $this->quickSearchForm?->getData();

        if ($data instanceof QuickSearchFormData === false || $data->text === null) {
            return null;
        }

        $term = trim($data->text);

        return $term === '' ? null : $term;
    }
}
