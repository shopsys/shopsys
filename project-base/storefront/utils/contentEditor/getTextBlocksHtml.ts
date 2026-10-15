import { TypeContentBlock } from 'types/contentEditor';

const collectHtml = (blocks: TypeContentBlock[]): string[] =>
    blocks.flatMap((block) => [
        ...(block.type === 'text' && typeof block.data.html === 'string' ? [block.data.html] : []),
        ...collectHtml((block.children ?? []).flat()),
    ]);

// HTML of all text blocks in document order, used where the article needs a description (meta description, og tags)
export const getTextBlocksHtml = (document: { blocks: TypeContentBlock[] } | null): string =>
    collectHtml(document?.blocks ?? []).join(' ');
