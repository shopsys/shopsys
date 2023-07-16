import { Icon } from 'components/Basic/Icon/Icon';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import useEmblaCarousel, { EmblaOptionsType } from 'embla-carousel-react';
import { ImageSizesFragmentApi, SimpleFlagFragmentApi, VideoTokenFragmentApi } from 'graphql/generated';
import { useCallback, useEffect, useState } from 'react';
import { twJoin } from 'tailwind-merge';

type ProductDetailGallerySliderProps = {
    galleryItems: ImageSizesFragmentApi[];
    flags: SimpleFlagFragmentApi[];
    videoIds?: VideoTokenFragmentApi[];
};

const sliderSettings = (): EmblaOptionsType => {
    return {
        dragFree: false,
        loop: false,
        startIndex: 0,
        containScroll: 'trimSnaps',
        slidesToScroll: 1,
    };
};

export const ProductDetailGallerySlider: FC<ProductDetailGallerySliderProps> = ({ galleryItems, flags, videoIds }) => {
    const [prevBtnEnabled, setPrevBtnEnabled] = useState(false);
    const [nextBtnEnabled, setNextBtnEnabled] = useState(false);
    const [selectedIndex, setSelectedIndex] = useState(0);
    const [scrollSnaps, setScrollSnaps] = useState<number[]>([]);
    const [emblaRef, emblaApi] = useEmblaCarousel(sliderSettings());

    const scrollPrev = useCallback(() => emblaApi?.scrollPrev(), [emblaApi]);
    const scrollNext = useCallback(() => emblaApi?.scrollNext(), [emblaApi]);
    const scrollTo = useCallback((index: number) => emblaApi && emblaApi.scrollTo(index), [emblaApi]);

    const onSelect = useCallback(() => {
        if (!emblaApi) {
            return;
        }
        setSelectedIndex(emblaApi.selectedScrollSnap());
        setPrevBtnEnabled(emblaApi.canScrollPrev());
        setNextBtnEnabled(emblaApi.canScrollNext());
    }, [emblaApi, setSelectedIndex]);

    useEffect(() => {
        if (!emblaApi) {
            return;
        }
        onSelect();
        setScrollSnaps(emblaApi.scrollSnapList());
        emblaApi.on('select', onSelect);
        emblaApi.on('reInit', onSelect);
    }, [emblaApi, setScrollSnaps, onSelect, videoIds]);

    return (
        <div className="p-2 lg:hidden">
            <div ref={emblaRef} className="w-full overflow-hidden">
                <div className="relative flex h-auto flex-row">
                    {galleryItems.map((galleryItem, index) => (
                        <div
                            key={index}
                            className="relative flex max-h-[250px] min-h-[250px] w-full min-w-0 flex-shrink-0 items-center justify-center sm:max-h-[300px] sm:min-h-[300px] md:max-h-[330px] md:min-h-[330px]"
                            data-src={galleryItem.sizes.find((size) => size.size === 'default')?.url}
                        >
                            <img
                                className="h-full w-full object-contain"
                                loading="lazy"
                                src={galleryItem.sizes.find((size) => size.size === 'default')?.url}
                            />
                        </div>
                    ))}
                    {videoIds !== undefined &&
                        videoIds.map((videoId, index) => {
                            return (
                                <div
                                    key={'video-' + index}
                                    className="relative flex w-full min-w-0 flex-shrink-0 items-center justify-center pb-[56.25%] sm:max-h-[300px] sm:min-h-[300px] md:max-h-[330px] md:min-h-[330px]"
                                    data-src=""
                                >
                                    <iframe
                                        className="absolute top-0 left-0 h-full w-full border-none"
                                        src={'https://www.youtube.com/embed/' + videoId.token}
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                        allowFullScreen
                                    />
                                </div>
                            );
                        })}
                </div>
                <div className="flex items-center justify-center pt-6 pr-4 pb-2">
                    {scrollSnaps.map((_, index) => (
                        <button
                            key={index}
                            className={twJoin(
                                'mr-1 h-3 w-3 flex-grow-0 basis-3 cursor-pointer rounded-full border-none bg-greyLighter',
                                index === selectedIndex && 'bg-greyDarker',
                            )}
                            onClick={() => scrollTo(index)}
                        />
                    ))}
                </div>
                <div className="absolute top-3 left-4 flex flex-col">
                    <ProductFlags flags={flags} />
                </div>
            </div>
            <ImageSliderControl onClick={scrollPrev} enabled={prevBtnEnabled} />
            <ImageSliderControl onClick={scrollNext} isNext enabled={nextBtnEnabled} />
        </div>
    );
};

type ImageSliderControlProps = {
    isNext?: boolean;
    onClick: () => void;
    enabled?: boolean;
};

const ImageSliderControl: FC<ImageSliderControlProps> = ({ isNext, onClick, enabled }) => (
    <button
        className={twJoin(
            'absolute top-1/2 z-above h-8 w-8 -translate-y-1/2  p-0 pt-1 text-white lg:h-10 lg:w-10 vl:h-14 vl:w-14',
            isNext ? 'right-0' : 'left-0',
            enabled ? 'bg-primaryDarker' : 'bg-primaryLight',
        )}
        onClick={onClick}
        disabled={!enabled}
    >
        <Icon iconType="icon" icon="Arrow" className={isNext ? '-rotate-90' : 'rotate-90'} />
    </button>
);
