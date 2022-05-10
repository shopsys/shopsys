import { FC, useState } from 'react';
import {
    ProductDetailGalleryFlagsStyled,
    ProductDetailGalleryMainImageStyled,
    ProductDetailGalleryThumbnailsItemStyled,
    ProductDetailGalleryThumbnailsStyled,
} from './ProductDetailGallery.style';
import SimpleReactLightbox, { SRLWrapper } from 'simple-react-lightbox';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import Image from 'components/Basic/Image';
import { ImageType } from 'types/image';
import { isElementVisible } from 'components/Helpers/isElementVisible';
import ProductDetailImageSlider from './ProductDetailImageSlider';
import ProductFlags from 'components/Blocks/Product/Flags/ProductFlags';
import { SimpleFlagType } from 'types/flag';
import { useGetWindowSize } from 'hooks/ui/UseGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/UseResizeWidthEffect';

type ProductDetailGalleryProps = {
    images: ImageType[];
    productName: string;
    flags: SimpleFlagType[];
};

/**
 * Product detail gallery with simple lightbox and beside thumbnails
 */
const ProductDetailGallery: FC<ProductDetailGalleryProps> = (props) => {
    const [isSliderVisible, setSliderVisibility] = useState(false);
    const { width } = useGetWindowSize();
    useResizeWidthEffect(
        width,
        desktopFirstSizes.tablet,
        () => setSliderVisibility(false),
        () => setSliderVisibility(true),
        () => setSliderVisibility(isElementVisible([{ min: 0, max: desktopFirstSizes.tablet }], width)),
    );

    if (props.images.length === 0) {
        return null;
    }

    const mainImage = props.images[0];
    const mainImageUrl = props.images[0].sizes?.find((size) => size.size === 'default')?.url;

    return isSliderVisible ? (
        <ProductDetailImageSlider galleryItems={props.images} flags={props.flags} />
    ) : (
        <SimpleReactLightbox>
            <SRLWrapper
                options={{
                    settings: {
                        overlayColor: 'rgba(11,11,11,0.65)',
                    },
                    buttons: {
                        showDownloadButton: false,
                        showAutoplayButton: false,
                        showThumbnailsButton: false,
                    },
                    thumbnails: {
                        showThumbnails: false,
                    },
                }}
            >
                <ProductDetailGalleryThumbnailsStyled>
                    {props.images.map(
                        (image, index) =>
                            index > 0 && (
                                <ProductDetailGalleryThumbnailsItemStyled key={index}>
                                    <a href={image.sizes?.find((size) => size.size === 'default')?.url}>
                                        <Image image={image} alt={props.productName} type="default" />
                                    </a>
                                </ProductDetailGalleryThumbnailsItemStyled>
                            ),
                    )}
                </ProductDetailGalleryThumbnailsStyled>
                <ProductDetailGalleryMainImageStyled>
                    <a href={mainImageUrl}>
                        <Image image={mainImage} alt={props.productName} type="default" maxHeight="400px" />
                    </a>
                    <ProductDetailGalleryFlagsStyled>
                        <ProductFlags flags={props.flags} />
                    </ProductDetailGalleryFlagsStyled>
                </ProductDetailGalleryMainImageStyled>
            </SRLWrapper>
        </SimpleReactLightbox>
    );
};

/* @component */
export default ProductDetailGallery;
