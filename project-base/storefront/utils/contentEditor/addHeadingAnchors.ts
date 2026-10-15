import { ArticleHeading } from 'types/articleHeading';
import { TypeContentBlock, TypeContentDocument } from 'types/contentEditor';
import {
    ARTICLE_INTRODUCTION_ANCHOR_ID,
    createHeadingId,
    getArticleHtmlHeadingAnchors,
} from 'utils/articleHeadingAnchors';

type DocumentWithHeadingAnchors = {
    document: TypeContentDocument | null;
    headings: ArticleHeading[];
};

export const addHeadingAnchors = (document: TypeContentDocument | null): DocumentWithHeadingAnchors => {
    const headings: ArticleHeading[] = [];
    const usedHeadingIds = new Set<string>([ARTICLE_INTRODUCTION_ANCHOR_ID]);

    // blocks are processed in document order and share the ids, so the heading ids are unique in the whole article
    const addAnchors = (blocks: TypeContentBlock[]): TypeContentBlock[] =>
        blocks.map((block) => {
            if (block.type === 'text' && typeof block.data.html === 'string') {
                const result = getArticleHtmlHeadingAnchors(block.data.html, usedHeadingIds);
                headings.push(...result.headings);

                return { ...block, data: { ...block.data, html: result.htmlWithHeadingAnchors } };
            }

            // only h2 headings are in the table of contents, like in the text blocks
            if (block.type === 'heading' && block.data.level === 'h2' && typeof block.data.text === 'string') {
                const title = block.data.text.trim();

                if (title !== '') {
                    const id = createHeadingId(title, usedHeadingIds);
                    headings.push({ id, title });

                    return { ...block, data: { ...block.data, anchorId: id } };
                }
            }

            return block.children ? { ...block, children: block.children.map(addAnchors) } : block;
        });

    return { document: document && { ...document, blocks: addAnchors(document.blocks) }, headings };
};
