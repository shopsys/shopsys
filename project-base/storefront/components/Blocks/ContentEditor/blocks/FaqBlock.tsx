import { BlockProps } from 'components/Blocks/ContentEditor/ContentRenderer';
import { TIDs } from 'cypress/tids';
import { FaqBlockData } from 'types/contentEditor';

export const FaqBlock: FC<BlockProps<FaqBlockData>> = ({ block }) => (
    <details className="group rounded-xl border border-border-default p-4" data-tid={TIDs.content_block_faq}>
        <summary className="cursor-pointer font-bold">{block.data.question}</summary>
        <div className="user-text mt-2" dangerouslySetInnerHTML={{ __html: block.data.answer }} />
    </details>
);
