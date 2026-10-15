import { Image } from 'components/Basic/Image/Image';
import { BlockProps } from 'components/Blocks/ContentEditor/ContentRenderer';
import { TIDs } from 'cypress/tids';
import { ImageBlockData } from 'types/contentEditor';
import { twMergeCustom } from 'utils/twMerge';

const positionClassNames = { left: 'mr-auto', center: 'mx-auto', right: 'ml-auto' };

export const ImageBlock: FC<BlockProps<ImageBlockData>> = ({ block }) => {
    const { url, width, height, alt, position, link, legacy } = block.data;

    if (!url) {
        return null;
    }

    const className = twMergeCustom('block h-auto max-w-full', positionClassNames[position]);
    // legacy images come from the old editor and are not in the media library, so there is nothing to resize
    const image = legacy ? (
        // biome-ignore lint/performance/noImgElement: see the comment above
        <img alt={alt} className={className} src={url} />
    ) : (
        <Image alt={alt} className={className} height={height} src={url} width={width} />
    );

    return (
        <div className="my-8 lg:my-10" data-tid={TIDs.content_block_image}>
            {link ? <a href={link}>{image}</a> : image}
        </div>
    );
};
