import { parseStreetWithHouseNumber } from 'utils/parsing/parseStreetWithHouseNumber';
import { describe, expect, test } from 'vitest';

describe('parseStreetWithHouseNumber', () => {
    test.each([
        ['Street 123', { street: 'Street', streetNumber: '123' }],
        ['Street 123/4', { street: 'Street', streetNumber: '123/4' }],
        ['123 Street', { street: 'Street', streetNumber: '123' }],
        ['17. listopadu 2/2', { street: '17. listopadu', streetNumber: '2/2' }],
        ['1. máje 5', { street: '1. máje', streetNumber: '5' }],
    ])('parses street with house number: %s', (street, expected) => {
        expect(parseStreetWithHouseNumber(street)).toEqual(expected);
    });

    test.each([
        'Street',
        '123',
        '17. listopadu',
    ])('keeps street without complete street and house number unchanged: %s', (street) => {
        expect(parseStreetWithHouseNumber(street)).toEqual({ street });
    });
});
