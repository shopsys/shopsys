import { BlockProps } from 'components/Blocks/ContentEditor/ContentRenderer';
import { TIDs } from 'cypress/tids';
import { MapBlockData } from 'types/contentEditor';

export const MapBlock: FC<BlockProps<MapBlockData>> = ({ block }) => (
    <iframe
        className="my-6 h-[366px] w-full max-w-full lg:my-8"
        data-tid={TIDs.content_block_map}
        loading="lazy"
        src={block.data.url}
        title="map"
    />
);
