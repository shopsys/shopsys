import 'keen-slider/keen-slider.min.css';
import {
    BannersSliderBoxStyled,
    BannersSliderDotControlsStyled,
    BannersSliderStyled,
    BannersSliderThumbnailControlsIconStyled,
    BannersSliderThumbnailControlsStyled,
} from './BannersSlider.style';
import { FC, useEffect, useRef, useState } from 'react';
import BannersSliderItem from 'components/Blocks/Banners/BannersSliderItem';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { ImageSizeType } from 'types/image';
import { SliderItemType } from 'types/sliderItem';
import { theme } from 'components/Theme/main';
import { useGetWindowSize } from 'hooks/ui/UseGetWindowSize';
import { useKeenSlider } from 'keen-slider/react';

type BannersSliderProps = {
    sliderItems: SliderItemType[];
};

const DEVICE_BREAKPOINT_SIZE = {
    size: 'tablet',
    query: 'queryTablet',
} as const;

const BannersSlider: FC<BannersSliderProps> = (props) => {
    const [loadedImageUrls, setLoadedImageUrls] = useState<{ [key: string]: boolean }>({});
    const [currentSlide, setCurrentSlide] = useState(0);
    const [pause, setPause] = useState(false);
    const timer = useRef<NodeJS.Timer | null>(null);
    const { width } = useGetWindowSize();
    const sliderBoxRef = useRef<HTMLDivElement>(null);
    const [sliderRef, slider] = useKeenSlider<HTMLDivElement>({
        loop: true,
        duration: 1000,
        breakpoints: {
            [theme.mediaQueries[DEVICE_BREAKPOINT_SIZE.query]]: {
                slidesPerView: 2,
                spacing: 15,
                centered: true,
            },
        },
        slideChanged: (slider) => {
            setCurrentSlide(slider.details().relativeSlide);
        },
        dragStart: () => {
            setPause(true);
        },
        dragEnd: () => {
            setPause(false);
        },
        created(slider) {
            setLoadedImageUrls((currentLoadedImageUrls) => {
                const newLoadedImageUrls = { ...currentLoadedImageUrls };
                const slidesPerView = slider.options().slidesPerView;
                if (slidesPerView !== undefined) {
                    for (let i = 0; i < slidesPerView; i++) {
                        newLoadedImageUrls[i] = true;
                    }

                    if (slider.options().centered) {
                        newLoadedImageUrls[props.sliderItems.length - 1] = true;
                    }
                }
                return newLoadedImageUrls;
            });
        },
    });

    useEffect(() => {
        setLoadedImageUrls((currentLoadedImageUrls) => {
            const newLoadedImageUrls = { ...currentLoadedImageUrls };
            newLoadedImageUrls[currentSlide] = true;

            // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
            if (slider !== null && slider.options().centered) {
                newLoadedImageUrls[Math.min(currentSlide + 1, props.sliderItems.length - 1)] = true;
            }
            return newLoadedImageUrls;
        });
    }, [currentSlide, props.sliderItems.length, slider]);

    useEffect(() => {
        const setPauseTrue = () => {
            setPause(true);
        };
        const setPauseFalse = () => {
            setPause(false);
        };

        const sliderBox = sliderBoxRef.current;

        if (sliderBox !== null) {
            sliderBox.addEventListener('mouseover', setPauseTrue);
            sliderBox.addEventListener('mouseout', setPauseFalse);
        }

        return () => {
            sliderBox?.removeEventListener('mouseover', setPauseTrue);
            sliderBox?.removeEventListener('mouseout', setPauseFalse);
        };
    }, [sliderRef]);

    useEffect(() => {
        timer.current = setInterval(() => {
            // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
            if (!pause && slider !== null) {
                slider.next();
            }
        }, 5000);
        return () => {
            if (timer.current !== null) {
                clearInterval(timer.current);
            }
        };
    }, [pause, slider]);

    const onMoveToSlideHandler = (newSlideIndex: number) => {
        slider.moveToSlide(slider.details().absoluteSlide - (currentSlide - newSlideIndex));
    };

    return (
        <BannersSliderBoxStyled ref={sliderBoxRef}>
            <BannersSliderStyled ref={sliderRef} className="keen-slider">
                {props.sliderItems.map((sliderItem, index) => (
                    <BannersSliderItem
                        key={index}
                        image={getBannersSliderItemImage(
                            sliderItem,
                            loadedImageUrls[index],
                            width > desktopFirstSizes[DEVICE_BREAKPOINT_SIZE.size],
                        )}
                        link={sliderItem.link}
                    />
                ))}
            </BannersSliderStyled>
            <BannersSliderThumbnailControlsStyled>
                {props.sliderItems.map((sliderItem, index) => (
                    <button
                        onClick={() => onMoveToSlideHandler(index)}
                        disabled={index === currentSlide % props.sliderItems.length}
                        key={sliderItem.uuid}
                    >
                        <BannersSliderThumbnailControlsIconStyled iconType="icon" icon="Triangle" />
                        {sliderItem.name}
                    </button>
                ))}
            </BannersSliderThumbnailControlsStyled>
            <BannersSliderDotControlsStyled>
                {props.sliderItems.map((sliderItem, index) => (
                    <button
                        onClick={() => onMoveToSlideHandler(index)}
                        disabled={index === currentSlide % props.sliderItems.length}
                        key={sliderItem.uuid}
                    />
                ))}
            </BannersSliderDotControlsStyled>
        </BannersSliderBoxStyled>
    );
};

export const getBannersSliderItemImage = (
    sliderItem: SliderItemType,
    isImageLoaded: boolean,
    desktopVariant: boolean,
): ImageSizeType | null => {
    const image = desktopVariant ? sliderItem.webImages : sliderItem.mobileImages;

    if (!isImageLoaded || image === null || image.sizes === null) {
        return null;
    }

    return image.sizes.find((i) => i.size === 'default') ?? null;
};

export default BannersSlider;
