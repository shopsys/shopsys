import { getDynamicPageQueryKey } from 'helpers/parsing/getDynamicPageQueryKey';
import { describe, expect, test } from 'vitest';

describe('getDynamicPageQueryKey test', () => {
    test('path without dynamic parameter should return undefined', () => {
        expect(getDynamicPageQueryKey('/no/dynamic-parts/in/this-path')).toBe(undefined);
    });

    test('path with a dynamic parameter should return it', () => {
        expect(getDynamicPageQueryKey('/pathname/with/[dynamic-key]')).toBe('dynamic-key');
    });

    test('path with two dynamic parameters should return the first one', () => {
        expect(getDynamicPageQueryKey('/pathname/with/[dynamic-key]/and/[another-one]')).toBe('dynamic-key');
    });
});
