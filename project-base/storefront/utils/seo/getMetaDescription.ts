export const META_DESCRIPTION_MAX_LENGTH = 160;

const HTML_ENTITIES: Record<string, string> = {
    amp: '&',
    lt: '<',
    gt: '>',
    quot: '"',
    apos: "'",
    nbsp: ' ',
};

const MAX_CODE_POINT = 0x10ffff;

/**
 * Numeric entity outside the Unicode range is kept as it is, String.fromCodePoint() would throw and take the whole
 * page down during the server-side rendering
 */
const safeFromCodePoint = (codePoint: number, entity: string): string =>
    Number.isInteger(codePoint) && codePoint >= 0 && codePoint <= MAX_CODE_POINT
        ? String.fromCodePoint(codePoint)
        : entity;

const decodeHtmlEntities = (text: string): string =>
    text.replace(/&(#x[0-9a-f]+|#\d+|[a-z]+);/gi, (entity, code: string) => {
        if (code.startsWith('#x') || code.startsWith('#X')) {
            return safeFromCodePoint(Number.parseInt(code.slice(2), 16), entity);
        }

        if (code.startsWith('#')) {
            return safeFromCodePoint(Number.parseInt(code.slice(1), 10), entity);
        }

        return HTML_ENTITIES[code.toLowerCase()] ?? entity;
    });

/**
 * Text of the HTML without the tags, with decoded entities and collapsed whitespace
 */
export const htmlToPlainText = (html: string | null | undefined): string => {
    if (!html) {
        return '';
    }

    return decodeHtmlEntities(html.replace(/<[^>]*>/g, ' '))
        .replace(/\s+/g, ' ')
        .trim();
};

/**
 * Cuts the text at the last whitespace fitting into the limit, so that no word is cut in half.
 * A first word longer than the limit is cut hard. Trailing punctuation left after the cut is removed.
 */
export const truncateToWholeWords = (text: string, maxLength: number): string => {
    if (text.length <= maxLength) {
        return text;
    }

    const candidate = text.slice(0, maxLength + 1);
    const lastWhitespacePosition = candidate.lastIndexOf(' ');
    const truncated =
        lastWhitespacePosition > 0 ? candidate.slice(0, lastWhitespacePosition) : text.slice(0, maxLength);

    return truncated.replace(/[\s,;:-]+$/, '');
};

/**
 * Meta description as set in the administration, or the plain text of the given HTML (the description of the entity,
 * the perex of a blog article, ...) truncated to whole words, so that every page has a description
 */
export const getMetaDescription = (
    metaDescription: string | null | undefined,
    fallbackHtml: string | null | undefined,
): string | null => {
    const trimmedMetaDescription = metaDescription?.trim();

    if (trimmedMetaDescription) {
        return trimmedMetaDescription;
    }

    const plainText = htmlToPlainText(fallbackHtml);

    if (!plainText) {
        return null;
    }

    return truncateToWholeWords(plainText, META_DESCRIPTION_MAX_LENGTH);
};
