import { ProductDetailGalleryFlagsStyled } from './ProductDetailGallery.style';
import {
    ImageSliderControlNextStyled,
    ImageSliderControlPreviousStyled,
    ProductDetailImageSliderBoxStyled,
    ProductDetailImageSliderBulletStyled,
    ProductDetailImageSliderBulletsWrapperStyled,
    ProductDetailImageSliderItemStyled,
    ProductDetailImageSliderStyled,
    SliderItemImageStyled,
} from './ProductDetailImageSlider.style';
import { ProductFlags } from 'components/Blocks/Product/Flags/ProductFlags';
import { theme } from 'components/Theme/main';
import 'keen-slider/keen-slider.min.css';
import { useKeenSlider } from 'keen-slider/react';
import lgThumbnail from 'lightgallery/plugins/thumbnail';
import LightGallery from 'lightgallery/react';
import { FC, useState } from 'react';
import { SimpleFlagType } from 'types/flag';
import { ImageType } from 'types/image';

type ProductDetailImageSliderProps = {
    galleryItems: ImageType[];
    flags: SimpleFlagType[];
};

export const ProductDetailImageSlider: FC<ProductDetailImageSliderProps> = ({ galleryItems, flags }) => {
    const [areControlsVisible, setAreControlsVisible] = useState<boolean | undefined>(false);
    const [currentSlide, setCurrentSlide] = useState(0);
    const [sliderRef, slider] = useKeenSlider<HTMLDivElement>({
        loop: false,
        duration: 1000,
        breakpoints: {
            [theme.mediaQueries.queryTablet]: {
                slidesPerView: 1,
                spacing: 0,
            },
        },
        slideChanged(slider) {
            setCurrentSlide(slider.details().relativeSlide);
        },
        move(slider) {
            setAreControlsVisible(slider.options().controls);
        },
    });

    const onMoveToNextSlideHandler = () => {
        slider.moveToSlide(currentSlide + 1);
    };

    const onMoveToPreviousSlideHandler = () => {
        slider.moveToSlide(currentSlide - 1);
    };

    return (
        <LightGallery mode="lg-fade" thumbnail plugins={[lgThumbnail]} selector=".lightboxItem">
            <ProductDetailImageSliderBoxStyled>
                <ProductDetailImageSliderStyled ref={sliderRef} className="keen-slider">
                    {galleryItems.map((galleryItem, index) => (
                        <ProductDetailImageSliderItemStyled
                            key={index}
                            className="keen-slider__slide lightboxItem"
                            data-src={galleryItem.sizes?.find((size) => size.size === 'default')?.url}
                        >
                            <SliderItemImageStyled
                                loading="lazy"
                                src={galleryItem.sizes?.find((size) => size.size === 'default')?.url}
                            />
                        </ProductDetailImageSliderItemStyled>
                    ))}
                </ProductDetailImageSliderStyled>
                <ProductDetailImageSliderBulletsWrapperStyled>
                    {galleryItems.map((galleryItem, index) => (
                        <ProductDetailImageSliderBulletStyled
                            key={index}
                            className={currentSlide === index ? 'isActive' : undefined}
                            onClick={() => slider.moveToSlide(index)}
                        />
                    ))}
                </ProductDetailImageSliderBulletsWrapperStyled>
                {/* eslint-disable-next-line @typescript-eslint/no-unnecessary-condition */}
                {slider !== null && areControlsVisible ? (
                    <>
                        <ImageSliderControlPreviousStyled onClick={onMoveToPreviousSlideHandler}>
                            p
                        </ImageSliderControlPreviousStyled>
                        <ImageSliderControlNextStyled onClick={onMoveToNextSlideHandler}>
                            n
                        </ImageSliderControlNextStyled>
                    </>
                ) : null}
                <ProductDetailGalleryFlagsStyled>
                    <ProductFlags flags={flags} />
                </ProductDetailGalleryFlagsStyled>
            </ProductDetailImageSliderBoxStyled>
        </LightGallery>
    );
};
