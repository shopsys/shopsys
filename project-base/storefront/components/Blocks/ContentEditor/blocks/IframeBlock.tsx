import { BlockProps } from 'components/Blocks/ContentEditor/ContentRenderer';
import { TIDs } from 'cypress/tids';
import { IframeBlockData } from 'types/contentEditor';

export const IframeBlock: FC<BlockProps<IframeBlockData>> = ({ block }) => (
    <iframe
        className="my-6 w-full max-w-full lg:my-8"
        data-tid={TIDs.content_block_iframe}
        height={block.data.height}
        loading="lazy"
        src={block.data.url}
        title="embedded content"
    />
);
