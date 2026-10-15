import { TypeContentBlock } from 'types/contentEditor';

const collectCatnums = (blocks: TypeContentBlock[]): string[] =>
    blocks.flatMap((block) => [
        ...(Array.isArray(block.data.catnums) ? (block.data.catnums as string[]) : []),
        ...collectCatnums((block.children ?? []).flat()),
    ]);

export const collectProductCatnums = (document: { blocks: TypeContentBlock[] } | null): string[] =>
    Array.from(new Set(collectCatnums(document?.blocks ?? [])));
