import { ProductDetailGallerySlider } from './ProductDetailGallerySlider';
import { Icon } from 'components/Basic/Icon/Icon';
import { Image } from 'components/Basic/Image/Image';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { isElementVisible } from 'components/Helpers/isElementVisible';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { ImageSizesFragmentApi, SimpleFlagFragmentApi, VideoTokenFragmentApi } from 'graphql/generated';
import { getFirstImageOrNull } from 'helpers/mappers/image';
import { useGetWindowSize } from 'hooks/ui/useGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/useResizeWidthEffect';
import dynamic from 'next/dynamic';
import { useState } from 'react';
import { twJoin } from 'tailwind-merge';

const Gallery = dynamic(() => import('components/Basic/Gallery/Gallery').then((component) => component.Gallery));

type ProductDetailGalleryProps = {
    images: ImageSizesFragmentApi[];
    productName: string;
    flags: SimpleFlagFragmentApi[];
    videoIds?: VideoTokenFragmentApi[];
};

export const ProductDetailGallery: FC<ProductDetailGalleryProps> = ({ flags, images, productName, videoIds }) => {
    const [isSliderVisible, setSliderVisibility] = useState(false);
    const { width } = useGetWindowSize();

    useResizeWidthEffect(
        width,
        desktopFirstSizes.tablet,
        () => setSliderVisibility(false),
        () => setSliderVisibility(true),
        () => setSliderVisibility(isElementVisible([{ min: 0, max: desktopFirstSizes.tablet }], width)),
    );

    if (!images.length && !videoIds) {
        return null;
    }

    const firstImage = getFirstImageOrNull(images);
    const mainImage = firstImage;
    const mainImageUrl = firstImage?.sizes.find((size) => size.size === 'default')?.url;

    return isSliderVisible ? (
        <ProductDetailGallerySlider galleryItems={images} flags={flags} videoIds={videoIds} />
    ) : (
        <Gallery selector=".lightboxItem">
            <div
                data-src={mainImageUrl}
                className={twJoin(
                    'hidden lg:relative lg:order-1 lg:block lg:overflow-hidden lg:rounded-xl lg:p-4',
                    mainImage && 'lightboxItem',
                )}
            >
                <Image image={mainImage} alt={mainImage?.name || productName} type="default" height="400px" />
                {!!flags.length && (
                    <div className="absolute top-3 left-4 flex flex-col">
                        <ProductFlags flags={flags} />
                    </div>
                )}
            </div>
            <div className="hidden lg:relative lg:order-none lg:mb-5 lg:flex lg:w-24 lg:flex-col lg:pr-6">
                {images.map(
                    (image, index) =>
                        index > 0 && (
                            <div
                                key={index}
                                className={twJoin(
                                    'lightboxItem relative block w-20 cursor-pointer lg:mb-3 lg:h-16 lg:rounded-md lg:bg-greyVeryLight lg:p-2 lg:transition lg:hover:bg-greyLighter',
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
                        ),
                )}
                {!!videoIds &&
                    videoIds.map((videoId) => {
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
                                className="lightboxItem relative block max-h-56 w-20 cursor-pointer lg:mb-3 lg:h-16 lg:rounded-md lg:bg-greyVeryLight lg:p-2 lg:transition lg:hover:bg-greyLighter"
                                data-poster={`https://img.youtube.com/vi/${videoId.token}/0.jpg`}
                                data-src={`https://www.youtube.com/embed/${videoId.token}`}
                            >
                                <Image image={videoImage} type="thumbnailSmall" alt={videoId.description} />
                                <div className="absolute top-4 left-6 flex h-8 w-8 items-center justify-center rounded-full bg-dark bg-opacity-50 text-white">
                                    <Icon iconType="icon" icon="Play" className="ml-1" />
                                </div>
                            </div>
                        );
                    })}
            </div>
        </Gallery>
    );
};
