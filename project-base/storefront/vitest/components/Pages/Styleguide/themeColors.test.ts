import { parseThemeColorBlocks } from 'components/Pages/Styleguide/themeColors';
import { describe, expect, test } from 'vitest';

const getFamilyNames = (css: string) =>
    parseThemeColorBlocks(css).map((block) => ({
        title: block.title,
        families: block.families.map((family) => family.name),
    }));

describe('parseThemeColorBlocks', () => {
    test('groups colors into the commented blocks and the families separated by blank lines', () => {
        const css = `@theme {
    --radius-md: 6px;

    /* Palette: neutrals */
    --color-base-black: #25283d;
    --color-base-white: #ffffff;

    --color-gray-50: #fafafa;
    --color-gray-100: #f6f6f6;

    /* Components */
    --color-button-primary-bg-default: var(--color-primary-500);
    --color-button-primary-text-default: var(--color-text-inverted);

    --color-table-bg-default: #ffffff;
    --color-table-bg-header: #0f00a0;
}`;

        expect(getFamilyNames(css)).toEqual([
            { title: 'Palette: neutrals', families: ['base', 'gray'] },
            { title: 'Components', families: ['button-primary', 'table'] },
        ]);
        expect(parseThemeColorBlocks(css)[1].families[0].colors[0]).toEqual({
            name: 'button-primary-bg-default',
            value: 'var(--color-primary-500)',
        });
    });

    test('keeps colors without a block comment in one default block', () => {
        const css = `    --color-flag-text: var(--color-text-inverted);

    --color-status-badge-bg-info: #dff1ff;
    --color-alert-bg-info: #eef5fe;`;

        expect(getFamilyNames(css)).toEqual([{ title: 'Colors', families: ['flag', 'status / alert'] }]);
    });
});
