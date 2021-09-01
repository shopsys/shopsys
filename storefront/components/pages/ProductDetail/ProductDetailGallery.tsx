import SimpleReactLightbox, { SRLWrapper } from 'simple-react-lightbox';
import {
    StyledProductDetailGalleryMainImage,
    StyledProductDetailGalleryThumbnails,
    StyledProductDetailGalleryThumbnailsItem,
} from './ProductDetailGallery.style';
import { FC } from 'react';
import ProductDetailImageSlider from './ProductDetailImageSlider';

const ProductDetailGallery: FC = () => {
    /* TODO PRG: join live data */
    const productDetailSliderItems = [
        {
            type: 'web',
            position: 1,
            size: 'default',
            url: 'http://placeimg.com/640/530/any?t=1',
            width: 968,
            height: 318,
        },
        {
            type: 'web',
            position: 2,
            size: 'default',
            url: 'http://placeimg.com/640/530/any?t=2',
            width: 968,
            height: 318,
        },
    ];

    if (
        productDetailSliderItems === undefined ||
        (Array.isArray(productDetailSliderItems) && productDetailSliderItems.length === 0)
    ) {
        return null;
    }

    return (
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
                <StyledProductDetailGalleryThumbnails>
                    <StyledProductDetailGalleryThumbnailsItem>
                        <a href="http://placeimg.com/640/530/any?t=1">
                            <img src="http://placeimg.com/64/53/any?t=1" alt="Umbrella" width={64} height={53} />
                        </a>
                    </StyledProductDetailGalleryThumbnailsItem>

                    <StyledProductDetailGalleryThumbnailsItem>
                        <a href="http://placeimg.com/640/530/any?t=2">
                            <img src="http://placeimg.com/64/53/any?t=2" alt="Umbrella" width={64} height={53} />
                        </a>
                    </StyledProductDetailGalleryThumbnailsItem>
                </StyledProductDetailGalleryThumbnails>
                <StyledProductDetailGalleryMainImage>
                    <a href="http://placeimg.com/640/530/any?t=3">
                        <img
                            src="http://placeimg.com/640/530/any?t=3"
                            alt="Picture of the author"
                            width={552}
                            height={454}
                        />
                    </a>
                </StyledProductDetailGalleryMainImage>
                <ProductDetailImageSlider galleryItems={productDetailSliderItems} />
            </SRLWrapper>
        </SimpleReactLightbox>
    );
};

export default ProductDetailGallery;
