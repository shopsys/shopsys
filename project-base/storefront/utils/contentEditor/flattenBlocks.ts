import { TypeContentBlock } from 'types/contentEditor';

// all blocks of the tree in document order, a block comes before the blocks in its slots
export const flattenBlocks = (blocks: TypeContentBlock[]): TypeContentBlock[] =>
    blocks.flatMap((block) => [block, ...flattenBlocks((block.children ?? []).flat())]);
