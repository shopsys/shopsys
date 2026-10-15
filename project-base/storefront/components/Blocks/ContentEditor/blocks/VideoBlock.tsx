import { BlockProps } from 'components/Blocks/ContentEditor/ContentRenderer';
import { TIDs } from 'cypress/tids';
import { VideoBlockData } from 'types/contentEditor';

export const VideoBlock: FC<BlockProps<VideoBlockData>> = ({ block }) => {
    const { provider, embedUrl } = block.data;

    if (!embedUrl) {
        return null;
    }

    return (
        <iframe
            allowFullScreen
            className="my-6 aspect-video w-full max-w-full lg:my-8"
            data-tid={TIDs.content_block_video}
            loading="lazy"
            src={embedUrl}
            title={provider ?? 'video'}
            {...(provider === 'youtube' && {
                allow: 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share',
                referrerPolicy: 'strict-origin-when-cross-origin',
            })}
        />
    );
};
