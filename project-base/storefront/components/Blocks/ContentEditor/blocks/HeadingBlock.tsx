import { BlockProps } from 'components/Blocks/ContentEditor/ContentRenderer';
import { TIDs } from 'cypress/tids';
import { HeadingBlockData } from 'types/contentEditor';

// the heading is rendered inside the user text typography, so it looks like a heading written in a text block
export const HeadingBlock: FC<BlockProps<HeadingBlockData>> = ({ block }) => {
    const Tag = block.data.level;

    return (
        <section className="user-text" data-tid={TIDs.content_block_heading}>
            <Tag id={block.data.anchorId}>{block.data.text}</Tag>
        </section>
    );
};
