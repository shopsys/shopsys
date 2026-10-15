import { ButtonLinkBlock } from 'components/Blocks/ContentEditor/blocks/ButtonLinkBlock';
import { ColumnsBlock } from 'components/Blocks/ContentEditor/blocks/ColumnsBlock';
import { HeadingBlock } from 'components/Blocks/ContentEditor/blocks/HeadingBlock';
import { IframeBlock } from 'components/Blocks/ContentEditor/blocks/IframeBlock';
import { ImageBlock } from 'components/Blocks/ContentEditor/blocks/ImageBlock';
import { MapBlock } from 'components/Blocks/ContentEditor/blocks/MapBlock';
import { ProductsBlock } from 'components/Blocks/ContentEditor/blocks/ProductsBlock';
import { TextBlock } from 'components/Blocks/ContentEditor/blocks/TextBlock';
import { VideoBlock } from 'components/Blocks/ContentEditor/blocks/VideoBlock';
import { BlockProps } from 'components/Blocks/ContentEditor/ContentRenderer';

// projects add or replace blocks by editing this map; the key is the block type from the content document
// every block component has its own data type, hence `any`
export const blockComponents: Record<string, FC<BlockProps<any>>> = {
    text: TextBlock,
    heading: HeadingBlock,
    image: ImageBlock,
    'button-link': ButtonLinkBlock,
    video: VideoBlock,
    map: MapBlock,
    iframe: IframeBlock,
    products: ProductsBlock,
    'columns-1-1': ColumnsBlock,
    'columns-1-2': ColumnsBlock,
    'columns-2-1': ColumnsBlock,
    'columns-1-1-1': ColumnsBlock,
};
