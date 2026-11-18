<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Search;

/**
 * The `searchable` option of a field that picks where the quick search looks for the text:
 *
 *     'property' => ['lastName', 'firstName', 'customerUser.id'],
 *     'searchable' => new Searchable(paths: ['lastName', 'firstName'], label: t('Reviewer name')),
 *
 * `'searchable' => true` is the same as `new Searchable()`, which searches in every property of the field.
 */
final readonly class Searchable
{
    /**
     * @param non-empty-list<string>|null $paths Properties of the field to search in, null for all of them
     * @param string|null $label Label of the field in the placeholder of the quick search, null for the label of the field
     */
    public function __construct(
        public ?array $paths = null,
        public ?string $label = null,
    ) {
    }
}
