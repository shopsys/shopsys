export type ThemeColor = { name: string; value: string };

export type ThemeColorFamily = { name: string; colors: ThemeColor[] };

export type ThemeColorBlock = { title: string; families: ThemeColorFamily[] };

const COLOR_DECLARATION = /^--color-([a-z0-9-]+):\s*(.+);$/;
const BLOCK_COMMENT = /^\/\*\s*(.+?)\s*\*\/$/;
const DEFAULT_BLOCK_TITLE = 'Colors';
// a family named by the component (table), not by the property its colors share (table-bg)
const PROPERTY_SEGMENTS = ['bg', 'border', 'text', 'icon'];

/**
 * Names a family by the name segments its colors share (neutral-50 … neutral-900 → neutral). A single color or a shared
 * property drops its last segment (flag-text → flag, table-bg-* → table), and colors without a shared start
 * are listed by their first segments.
 */
const getFamilyName = (colors: ThemeColor[]) => {
    const segmentLists = colors.map((color) => color.name.split('-'));
    const firstSegments = segmentLists[0];
    const isSharedSegment = (segment: string, index: number) =>
        segmentLists.every((segments) => segments[index] === segment);
    const firstDifferentIndex = firstSegments.findIndex((segment, index) => !isSharedSegment(segment, index));
    const sharedSegments = firstDifferentIndex === -1 ? firstSegments : firstSegments.slice(0, firstDifferentIndex);
    const isLastSegmentPropertyOrSingleColor =
        colors.length === 1 || PROPERTY_SEGMENTS.includes(sharedSegments[sharedSegments.length - 1]);
    const nameSegments = isLastSegmentPropertyOrSingleColor ? sharedSegments.slice(0, -1) : sharedSegments;

    if (nameSegments.length > 0) {
        return nameSegments.join('-');
    }

    const distinctFirstSegments = segmentLists
        .map((segments) => segments[0])
        .filter((segment, index, allSegments) => allSegments.indexOf(segment) === index);

    return distinctFirstSegments.join(' / ');
};

/**
 * Reads the color tokens of theme.css in their order: a comment starts a titled block,
 * and a blank line separates the families inside a block.
 */
export const parseThemeColorBlocks = (css: string): ThemeColorBlock[] => {
    const blocks: ThemeColorBlock[] = [];
    let familyColors: ThemeColor[] = [];

    const getCurrentBlock = () => {
        if (blocks.length === 0) {
            blocks.push({ title: DEFAULT_BLOCK_TITLE, families: [] });
        }

        return blocks[blocks.length - 1];
    };

    const closeFamily = () => {
        if (familyColors.length > 0) {
            getCurrentBlock().families.push({ name: getFamilyName(familyColors), colors: familyColors });
            familyColors = [];
        }
    };

    for (const line of css.split('\n').map((rawLine) => rawLine.trim())) {
        const blockTitle = line.match(BLOCK_COMMENT)?.[1];
        const colorDeclaration = line.match(COLOR_DECLARATION);

        if (blockTitle) {
            closeFamily();
            blocks.push({ title: blockTitle, families: [] });
        } else if (colorDeclaration) {
            familyColors.push({ name: colorDeclaration[1], value: colorDeclaration[2] });
        } else {
            closeFamily();
        }
    }

    closeFamily();

    return blocks.filter((block) => block.families.length > 0);
};
