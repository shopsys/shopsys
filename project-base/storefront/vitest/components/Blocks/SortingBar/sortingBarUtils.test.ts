import { splitSortOptions } from 'components/Blocks/SortingBar/sortingBarUtils';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import { describe, expect, test } from 'vitest';

const ALL_SORT_OPTIONS = [
    TypeProductOrderingModeEnum.Priority,
    TypeProductOrderingModeEnum.PriceAsc,
    TypeProductOrderingModeEnum.PriceDesc,
    TypeProductOrderingModeEnum.Relevance,
    TypeProductOrderingModeEnum.NameAsc,
    TypeProductOrderingModeEnum.NameDesc,
];

describe('splitSortOptions', () => {
    test('keeps all four options visible', () => {
        const sortOptions = [
            TypeProductOrderingModeEnum.Priority,
            TypeProductOrderingModeEnum.PriceAsc,
            TypeProductOrderingModeEnum.PriceDesc,
            TypeProductOrderingModeEnum.Relevance,
        ];

        expect(splitSortOptions(sortOptions, TypeProductOrderingModeEnum.Priority)).toEqual({
            visibleSortOptions: sortOptions,
            moreSortOptions: [],
        });
    });

    test('moves the options after the fourth under "More"', () => {
        expect(splitSortOptions(ALL_SORT_OPTIONS, TypeProductOrderingModeEnum.Priority)).toEqual({
            visibleSortOptions: [
                TypeProductOrderingModeEnum.Priority,
                TypeProductOrderingModeEnum.PriceAsc,
                TypeProductOrderingModeEnum.PriceDesc,
                TypeProductOrderingModeEnum.Relevance,
            ],
            moreSortOptions: [TypeProductOrderingModeEnum.NameAsc, TypeProductOrderingModeEnum.NameDesc],
        });
    });

    test('swaps a selected option from "More" with the last visible one', () => {
        expect(splitSortOptions(ALL_SORT_OPTIONS, TypeProductOrderingModeEnum.NameDesc)).toEqual({
            visibleSortOptions: [
                TypeProductOrderingModeEnum.Priority,
                TypeProductOrderingModeEnum.PriceAsc,
                TypeProductOrderingModeEnum.PriceDesc,
                TypeProductOrderingModeEnum.NameDesc,
            ],
            moreSortOptions: [TypeProductOrderingModeEnum.Relevance, TypeProductOrderingModeEnum.NameAsc],
        });
    });

    test('ignores a selected option that is not offered', () => {
        const sortOptions = ALL_SORT_OPTIONS.filter(
            (sortOption) => sortOption !== TypeProductOrderingModeEnum.NameDesc,
        );

        expect(splitSortOptions(sortOptions, TypeProductOrderingModeEnum.NameDesc).visibleSortOptions).toEqual([
            TypeProductOrderingModeEnum.Priority,
            TypeProductOrderingModeEnum.PriceAsc,
            TypeProductOrderingModeEnum.PriceDesc,
            TypeProductOrderingModeEnum.Relevance,
        ]);
    });
});
