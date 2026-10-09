import { Kind } from 'graphql';
import { AutocompleteSearchQueryDocument } from 'graphql/requests/search/queries/AutocompleteSearchQuery.generated';
import { SearchQueryDocument } from 'graphql/requests/search/queries/SearchQuery.generated';
import { describe, expect, test } from 'vitest';
import { getFields, getSelectionAt } from 'vitest/helpers/graphqlSelections';

describe('autocomplete article data requirements', () => {
    test.each([
        ['ArticleSite', ['name', 'slug', 'uuid']],
        ['BlogArticle', ['name', 'slug']],
    ])('fetches only link fields and existing cache identity for %s', (typeName, expectedFields) => {
        const selection = getSelectionAt(AutocompleteSearchQueryDocument, ['articlesSearch']);
        const branch = selection.selections.find(
            (node) => node.kind === Kind.INLINE_FRAGMENT && node.typeCondition?.name.value === typeName,
        );

        expect(selection.selections).toContainEqual(
            expect.objectContaining({ kind: Kind.FIELD, name: expect.objectContaining({ value: '__typename' }) }),
        );
        expect(branch?.kind).toBe(Kind.INLINE_FRAGMENT);
        if (branch?.kind !== Kind.INLINE_FRAGMENT) {
            throw new Error(`Missing article type branch ${typeName}`);
        }
        expect(
            getFields(AutocompleteSearchQueryDocument, branch.selectionSet)
                .map((field) => field.name.value)
                .sort(),
        ).toEqual(expectedFields);
    });

    test('keeps article images and metadata in full search', () => {
        const selection = getSelectionAt(SearchQueryDocument, ['articlesSearch']);

        expect(getFields(SearchQueryDocument, selection).map((field) => field.name.value)).toEqual(
            expect.arrayContaining(['mainImage', 'placement', 'external']),
        );
    });
});
