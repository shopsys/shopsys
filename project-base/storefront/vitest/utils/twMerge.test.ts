import { twMergeCustom } from 'utils/twMerge';
import { describe, expect, test } from 'vitest';

describe('twMergeCustom', () => {
    test('lets a later radius override a theme radius token and the other way round', () => {
        expect(twMergeCustom('rounded-input', 'rounded-md')).toBe('rounded-md');
        expect(twMergeCustom('rounded-md', 'rounded-input')).toBe('rounded-input');
        expect(twMergeCustom('rounded-2xl', 'rounded-tag')).toBe('rounded-tag');
    });

    test('keeps a radius of one side next to a theme radius token', () => {
        expect(twMergeCustom('rounded-input', 'rounded-b-none')).toBe('rounded-input rounded-b-none');
    });

    test('treats the font weight roles as font weights, not as font families', () => {
        expect(twMergeCustom('font-semibold', 'font-heading')).toBe('font-heading');
        expect(twMergeCustom('font-action', 'font-bold')).toBe('font-bold');
        expect(twMergeCustom('font-default', 'font-heading')).toBe('font-default font-heading');
        expect(twMergeCustom('font-semibold', 'font-ui')).toBe('font-ui');
        expect(twMergeCustom('font-secondary', 'font-price')).toBe('font-secondary font-price');
    });

    test('lets a later heading class override an earlier one', () => {
        expect(twMergeCustom('h4 contents', 'h5')).toBe('contents h5');
        expect(twMergeCustom('h3 outline-hidden', 'h2')).toBe('outline-hidden h2');
    });
});
