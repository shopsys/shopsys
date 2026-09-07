import { parseCatnums } from 'utils/parsing/grapesJsParser';
import { describe, expect, test } from 'vitest';

describe('parseCatnums', () => {
    test('extracts unique catalog numbers from consecutive product blocks', () => {
        const text =
            '<p>Introduction</p>|||[gjc-comp-ProductList&#61;9177759,5964035]||||||[gjc-comp-ProductList&#61;9177759,9176508,5965879P,532564]|||<p>Media contact</p>';

        const catnums = parseCatnums(text);

        expect(catnums).toEqual(['9177759', '5964035', '9176508', '5965879P', '532564']);
    });
});
