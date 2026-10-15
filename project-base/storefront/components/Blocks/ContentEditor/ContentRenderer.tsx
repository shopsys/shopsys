import { blockComponents } from 'components/Blocks/ContentEditor/blockComponents';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { TypeContentBlock } from 'types/contentEditor';

export type BlockProps<TData = Record<string, unknown>> = {
    block: TypeContentBlock<TData>;
    products: TypeListedProductFragment[];
    areProductsFetching: boolean;
};

type ContentRendererProps = {
    // only the blocks are read, so a column slot can be rendered as `{ blocks: slot }`
    document: { blocks: TypeContentBlock[] } | null;
    products: TypeListedProductFragment[];
    areProductsFetching: boolean;
};

export const ContentRenderer: FC<ContentRendererProps> = ({ document, products, areProductsFetching }) => {
    if (!document) {
        return null;
    }

    return (
        <div className="[&>*:last-child]:mb-0">
            {document.blocks.map((block) => {
                const BlockComponent = blockComponents[block.type];

                if (!BlockComponent) {
                    if (process.env.NODE_ENV === 'development') {
                        // biome-ignore lint/suspicious/noConsole: a missing block component is a developer mistake
                        console.warn(`ContentRenderer: no component registered for block type "${block.type}".`);
                    }

                    return null;
                }

                return (
                    <BlockComponent
                        key={block.id}
                        areProductsFetching={areProductsFetching}
                        block={block}
                        products={products}
                    />
                );
            })}
        </div>
    );
};
