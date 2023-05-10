import { ImageGallery } from 'components/Basic/ImageGallery/ImageGallery';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { mediaQueries } from 'components/Theme/mediaQueries';
import { ImageSizesFragmentApi, SimpleFlagFragmentApi } from 'graphql/generated';
import 'keen-slider/keen-slider.min.css';
import { useKeenSlider } from 'keen-slider/react';
import { useState } from 'react';
import { twJoin } from 'tailwind-merge';

type ProductDetailImageSliderProps = {
    galleryItems: ImageSizesFragmentApi[];
    flags: SimpleFlagFragmentApi[];
};

export const ProductDetailImageSlider: FC<ProductDetailImageSliderProps> = ({ galleryItems, flags }) => {
    const [areControlsVisible, setAreControlsVisible] = useState<boolean | undefined>(false);
    const [currentSlide, setCurrentSlide] = useState(0);
    const [sliderRef, slider] = useKeenSlider<HTMLDivElement>({
        loop: false,
        duration: 1000,
        breakpoints: {
            [mediaQueries.queryTablet]: {
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
        <ImageGallery selector=".lightboxItem">
            <div className="relative flex w-full flex-col pb-0 lg:hidden">
                <div ref={sliderRef} className="keen-slider w-full cursor-pointer lg:w-[calc(100%-307px)] ">
                    {galleryItems.map((galleryItem, index) => {
                        const galleryImage = galleryItem.sizes.find((size) => size.size === 'default');

                        return (
                            <div
                                key={index}
                                className="keen-slider__slide lightboxItem flex max-h-[250px] min-h-[250px] w-full items-center justify-center sm:max-h-[300px] sm:min-h-[300px] md:max-h-[330px] md:min-h-[330px]"
                                data-src={galleryImage?.url}
                            >
                                <img
                                    className="h-full w-full object-contain"
                                    loading="lazy"
                                    src={galleryImage?.url}
                                    alt={galleryItem.name || ''}
                                />
                            </div>
                        );
                    })}
                </div>
                <div className="flex items-center justify-center pt-6 pr-4 pb-2">
                    {galleryItems.map((_, index) => (
                        <button
                            key={index}
                            className={twJoin(
                                'mr-1 h-3 w-3 flex-grow-0 basis-3 cursor-pointer rounded-full border-none bg-greyLighter',
                                currentSlide === index && 'bg-primaryLight',
                            )}
                            onClick={() => slider.moveToSlide(index)}
                        />
                    ))}
                </div>
                {/* eslint-disable-next-line @typescript-eslint/no-unnecessary-condition */}
                {slider !== null && areControlsVisible ? (
                    <>
                        <ImageSliderControl onClick={onMoveToPreviousSlideHandler}>p</ImageSliderControl>
                        <ImageSliderControl onClick={onMoveToNextSlideHandler} isNext>
                            n
                        </ImageSliderControl>
                    </>
                ) : null}
                <div className="absolute top-3 left-4 flex flex-col">
                    <ProductFlags flags={flags} />
                </div>
            </div>
        </ImageGallery>
    );
};

type ImageSliderControlProps = {
    isNext?: boolean;
    onClick: () => void;
};

const ImageSliderControl: FC<ImageSliderControlProps> = ({ children, isNext, onClick }) => (
    <button
        className={twJoin(
            'absolute top-[calc(50%-16px)] h-8 w-8 cursor-pointer rounded-sm border-none bg-greyDark text-creamWhite outline-none transition hover:bg-greyDarker',
            isNext ? 'right-0' : 'left-0',
        )}
        onClick={onClick}
    >
        {children}
    </button>
);
