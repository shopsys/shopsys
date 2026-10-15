import { TypeContentDocument } from 'types/contentEditor';

// the content is a JSON scalar in the generated types, so the shape of the document is only asserted here
export const toContentDocument = (content: unknown): TypeContentDocument | null =>
    (content as TypeContentDocument | null | undefined) ?? null;
