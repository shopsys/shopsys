import { ProductDetailImageSlider } from './ProductDetailImageSlider';
import { Image } from 'components/Basic/Image/Image';
import { ProductFlags } from 'components/Blocks/Product/Flags/ProductFlags';
import { isElementVisible } from 'components/Helpers/isElementVisible';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { ImageSizesFragmentApi } from 'graphql/generated';
import { getFirstImageOrNull } from 'helpers/mappers/image';
import { useGetWindowSize } from 'hooks/ui/useGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/useResizeWidthEffect';
import lgThumbnail from 'lightgallery/plugins/thumbnail';
import LightGallery from 'lightgallery/react';
import { useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { SimpleFlagType } from 'types/flag';

type ProductDetailGalleryProps = {
    images: ImageSizesFragmentApi[];
    productName: string;
    flags: SimpleFlagType[];
};

export const ProductDetailGallery: FC<ProductDetailGalleryProps> = ({ flags, images, productName }) => {
    const [isSliderVisible, setSliderVisibility] = useState(false);
    const { width } = useGetWindowSize();

    useResizeWidthEffect(
        width,
        desktopFirstSizes.tablet,
        () => setSliderVisibility(false),
        () => setSliderVisibility(true),
        () => setSliderVisibility(isElementVisible([{ min: 0, max: desktopFirstSizes.tablet }], width)),
    );

    if (images.length === 0) {
        return null;
    }

    const firstImage = getFirstImageOrNull(images);
    const mainImage = firstImage;
    const mainImageUrl = firstImage?.sizes.find((size) => size.size === 'default')?.url;

    return isSliderVisible ? (
        <ProductDetailImageSlider galleryItems={images} flags={flags} />
    ) : (
        <LightGallery mode="lg-fade" thumbnail plugins={[lgThumbnail]} selector=".lightboxItem">
            <div
                data-src={mainImageUrl}
                className="lightboxItem hidden lg:relative lg:order-1 lg:block lg:overflow-hidden lg:rounded-xl lg:p-4"
            >
                <Image image={mainImage} alt={productName} type="default" maxHeight="400px" />
                <div className="absolute top-3 left-4 flex flex-col">
                    <ProductFlags flags={flags} />
                </div>
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
                                    alt={productName}
                                    type="default"
                                    className="max-h-56 lg:absolute lg:left-0 lg:top-0 lg:right-0 lg:bottom-0 lg:m-auto lg:h-auto lg:max-h-full lg:w-auto lg:max-w-full lg:mix-blend-multiply"
                                />
                            </div>
                        ),
                )}
            </div>
        </LightGallery>
    );
};
