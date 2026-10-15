import { BlockProps, ContentRenderer } from 'components/Blocks/ContentEditor/ContentRenderer';
import { TIDs } from 'cypress/tids';
import { ColumnsBlockData } from 'types/contentEditor';

const layoutClassNames: Record<ColumnsBlockData['layout'], string> = {
    '1/2-1/2': 'lg:grid-cols-2',
    '1/3-2/3': 'lg:grid-cols-[1fr_2fr]',
    '2/3-1/3': 'lg:grid-cols-[2fr_1fr]',
    '1/3-1/3-1/3': 'lg:grid-cols-3',
};

export const ColumnsBlock: FC<BlockProps<ColumnsBlockData>> = ({ block, products, areProductsFetching }) => (
    <div
        className={`my-8 grid grid-cols-1 gap-6 lg:my-10 lg:gap-10 ${layoutClassNames[block.data.layout]}`}
        data-tid={TIDs.content_block_columns}
    >
        {(block.children ?? []).map((slotBlocks, slotIndex) => (
            <div key={slotIndex} className="min-w-0">
                <ContentRenderer
                    areProductsFetching={areProductsFetching}
                    document={{ blocks: slotBlocks }}
                    products={products}
                />
            </div>
        ))}
    </div>
);
