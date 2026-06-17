import { type ArticleHeading } from 'types/articleHeading';

type ArticleHtmlHeadingAnchors = {
    headings: ArticleHeading[];
    htmlWithHeadingAnchors: string;
};

const HEADING_REGEX = /<h2\b([^>]*)>([\s\S]*?)<\/h2>/gi;
const ID_ATTRIBUTE_REGEX = /\sid=(["'])(.*?)\1/i;

const decodeHtmlEntities = (value: string): string => {
    const htmlEntities: Record<string, string> = {
        amp: '&',
        gt: '>',
        lt: '<',
        nbsp: ' ',
        quot: '"',
    };

    return value.replace(/&(#x?[0-9a-f]+|[a-z]+);/gi, (entity, entityValue: string) => {
        const normalizedEntityValue = entityValue.toLowerCase();

        if (normalizedEntityValue.startsWith('#x')) {
            return String.fromCodePoint(Number.parseInt(entityValue.slice(2), 16));
        }

        if (normalizedEntityValue.startsWith('#')) {
            return String.fromCodePoint(Number.parseInt(entityValue.slice(1), 10));
        }

        return htmlEntities[entityValue.toLowerCase()] ?? entity;
    });
};

const getPlainHeadingTitle = (headingHtml: string): string =>
    decodeHtmlEntities(
        headingHtml
            .replace(/<[^>]*>/g, ' ')
            .replace(/\s+/g, ' ')
            .trim(),
    );

const createHeadingId = (headingTitle: string, existingIds: Set<string>): string => {
    const normalizedTitle = headingTitle
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '');
    const baseId = normalizedTitle || 'section';
    let headingId = baseId;
    let counter = 2;

    while (existingIds.has(headingId)) {
        headingId = `${baseId}-${counter}`;
        counter++;
    }

    existingIds.add(headingId);

    return headingId;
};

export const getArticleHtmlHeadingAnchors = (htmlString: string): ArticleHtmlHeadingAnchors => {
    const usedHeadingIds = new Set<string>();
    const headings: ArticleHeading[] = [];

    const htmlWithHeadingAnchors = htmlString.replace(HEADING_REGEX, (heading, attributes: string, content: string) => {
        const headingTitle = getPlainHeadingTitle(content);

        if (headingTitle === '') {
            return heading;
        }

        const headingId = createHeadingId(headingTitle, usedHeadingIds);
        const attributesWithoutId = attributes.replace(ID_ATTRIBUTE_REGEX, '');

        headings.push({
            id: headingId,
            title: headingTitle,
        });

        return `<h2 id="${headingId}"${attributesWithoutId}>${content}</h2>`;
    });

    return {
        headings,
        htmlWithHeadingAnchors,
    };
};
