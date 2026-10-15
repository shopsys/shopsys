import { BlockProps } from 'components/Blocks/ContentEditor/ContentRenderer';
import { TIDs } from 'cypress/tids';
import { TextBlockData } from 'types/contentEditor';

export const TextBlock: FC<BlockProps<TextBlockData>> = ({ block }) => (
    <section
        className="user-text"
        data-tid={TIDs.content_block_text}
        dangerouslySetInnerHTML={{ __html: block.data.html }}
    />
);
