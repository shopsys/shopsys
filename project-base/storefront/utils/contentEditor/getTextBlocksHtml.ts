import { TypeContentBlock } from 'types/contentEditor';
import { flattenBlocks } from 'utils/contentEditor/flattenBlocks';

// HTML of all text blocks in document order, used where the article needs a description (meta description, og tags)
export const getTextBlocksHtml = (document: { blocks: TypeContentBlock[] } | null): string =>
    flattenBlocks(document?.blocks ?? [])
        .flatMap((block) => (block.type === 'text' && typeof block.data.html === 'string' ? [block.data.html] : []))
        .join(' ');
