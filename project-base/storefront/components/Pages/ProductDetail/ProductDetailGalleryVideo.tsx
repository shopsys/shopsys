import { ImageSizesFragmentApi, VideoTokenFragmentApi } from 'graphql/generated';
import { PlayIcon } from 'components/Basic/Icon/IconsSvg';
import { Image } from 'components/Basic/Image/Image';

type ProductDetailGalleryVideoProps = {
    videoId: VideoTokenFragmentApi;
};

export const ProductDetailGalleryVideo: FC<ProductDetailGalleryVideoProps> = ({ videoId }) => {
    const videoImage: ImageSizesFragmentApi = {
        __typename: 'Image',
        sizes: [
            {
                __typename: 'ImageSize',
                size: 'default',
                url: `https://img.youtube.com/vi/${videoId.token}/0.jpg`,
                width: 480,
                height: 360,
                additionalSizes: [
                    {
                        __typename: 'AdditionalSize',
                        width: 480,
                        height: 360,
                        media: 'only screen and (-webkit-min-device-pixel-ratio: 1.5)',
                        url: `https://img.youtube.com/vi/${videoId.token}/0.jpg`,
                    },
                ],
            },
            {
                __typename: 'ImageSize',
                size: 'thumbnailSmall',
                url: `https://img.youtube.com/vi/${videoId.token}/1.jpg`,
                width: 120,
                height: 90,
                additionalSizes: [
                    {
                        __typename: 'AdditionalSize',
                        width: 120,
                        height: 90,
                        media: 'only screen and (-webkit-min-device-pixel-ratio: 1.5)',
                        url: `https://img.youtube.com/vi/${videoId.token}/1.jpg`,
                    },
                ],
            },
        ],
        name: null,
    };

    return (
        <div
            key={videoId.token}
            className="lightboxItem relative flex h-16 w-20 cursor-pointer items-center lg:rounded lg:bg-greyVeryLight lg:transition lg:hover:bg-greyLighter"
            data-poster={`https://img.youtube.com/vi/${videoId.token}/0.jpg`}
            data-src={`https://www.youtube.com/embed/${videoId.token}`}
        >
            <Image image={videoImage} type="thumbnailSmall" alt={videoId.description} />

            <PlayIcon className="absolute top-1/2 left-1/2  flex h-8 w-8 -translate-y-1/2 -translate-x-1/2 items-center justify-center rounded-full bg-dark bg-opacity-50 text-white" />
        </div>
    );
};
