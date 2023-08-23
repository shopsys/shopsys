import { Gallery } from 'components/Basic/Gallery/Gallery';
import { PlayIcon } from 'components/Basic/Icon/IconsSvg';
import { Image } from 'components/Basic/Image/Image';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { ImageSizesFragmentApi, SimpleFlagFragmentApi, VideoTokenFragmentApi } from 'graphql/generated';
import { twJoin } from 'tailwind-merge';

type ProductDetailGalleryImagesProps = {
    productName: string;
    images: ImageSizesFragmentApi[];
    flags: SimpleFlagFragmentApi[];
    videoIds?: VideoTokenFragmentApi[];
};

export const ProductDetailGalleryImages: FC<ProductDetailGalleryImagesProps> = ({
    productName,
    flags,
    images,
    videoIds,
}) => {
    if (!images.length && !videoIds) {
        return null;
    }

    const [firstImage, ...additionalImages] = images;
    const mainImage = images.length ? firstImage : undefined;
    const mainImageUrl = mainImage?.sizes.find((size) => size.size === 'default')?.url;

    return (
        <Gallery selector=".lightboxItem">
            <div
                data-src={mainImageUrl}
                className={twJoin(
                    'hidden lg:relative lg:order-1 lg:block lg:overflow-hidden lg:rounded lg:p-4',
                    additionalImages.length && 'lightboxItem',
                )}
            >
                <Image image={mainImage} alt={mainImage?.name || productName} type="default" height="400px" />

                {!!flags.length && (
                    <div className="absolute top-3 left-4 flex flex-col">
                        <ProductFlags flags={flags} />
                    </div>
                )}
            </div>

            {!!(additionalImages.length || videoIds?.length) && (
                <div className="hidden lg:relative lg:order-none lg:mb-5 lg:flex lg:w-24 lg:flex-col lg:pr-6">
                    {!!additionalImages.length &&
                        additionalImages.map((image, index) => (
                            <div
                                key={index}
                                className={twJoin(
                                    'lightboxItem relative block w-20 cursor-pointer lg:mb-3 lg:h-16 lg:rounded lg:bg-greyVeryLight lg:p-2 lg:transition lg:hover:bg-greyLighter',
                                    index > 6 && 'hidden',
                                )}
                                data-src={image.sizes.find((size) => size.size === 'default')?.url}
                            >
                                <Image
                                    image={image}
                                    alt={image.name || `${productName}-${index}`}
                                    type="default"
                                    className="max-h-56 lg:absolute lg:left-0 lg:top-0 lg:right-0 lg:bottom-0 lg:m-auto lg:h-auto lg:max-h-full lg:w-auto lg:max-w-full lg:mix-blend-multiply"
                                />
                            </div>
                        ))}

                    {videoIds?.map((videoId) => {
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
                                className="lightboxItem relative block max-h-56 w-20 cursor-pointer lg:mb-3 lg:h-16 lg:rounded lg:bg-greyVeryLight lg:p-2 lg:transition lg:hover:bg-greyLighter"
                                data-poster={`https://img.youtube.com/vi/${videoId.token}/0.jpg`}
                                data-src={`https://www.youtube.com/embed/${videoId.token}`}
                            >
                                <Image image={videoImage} type="thumbnailSmall" alt={videoId.description} />

                                <div className="absolute top-4 left-6 flex h-8 w-8 items-center justify-center rounded-full bg-dark bg-opacity-50 text-white">
                                    <PlayIcon className="ml-1" />
                                </div>
                            </div>
                        );
                    })}
                </div>
            )}
        </Gallery>
    );
};
