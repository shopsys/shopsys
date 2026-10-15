import matchesPickerFilter from './matchesPickerFilter';

describe('matchesPickerFilter', () => {
    test('matches a part of the label regardless of case', () => {
        expect(matchesPickerFilter('Two columns', 'COL')).toBe(true);
    });

    test('matches without diacritics in both directions', () => {
        expect(matchesPickerFilter('Tlačítko', 'tlacit')).toBe(true);
        expect(matchesPickerFilter('Button', 'bütt')).toBe(true);
    });

    test('ignores surrounding spaces and matches everything for an empty query', () => {
        expect(matchesPickerFilter('Image', '  ima ')).toBe(true);
        expect(matchesPickerFilter('Image', '')).toBe(true);
        expect(matchesPickerFilter('Image', '   ')).toBe(true);
    });

    test('does not match a different label', () => {
        expect(matchesPickerFilter('Image', 'video')).toBe(false);
    });
});
