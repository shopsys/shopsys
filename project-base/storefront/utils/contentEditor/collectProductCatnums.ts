import { TypeContentBlock } from 'types/contentEditor';
import { flattenBlocks } from 'utils/contentEditor/flattenBlocks';

export const collectProductCatnums = (document: { blocks: TypeContentBlock[] } | null): string[] =>
    Array.from(
        new Set(
            flattenBlocks(document?.blocks ?? []).flatMap((block) =>
                Array.isArray(block.data.catnums) ? (block.data.catnums as string[]) : [],
            ),
        ),
    );
