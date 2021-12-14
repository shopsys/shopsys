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
import { SliderItem } from 'types/sliderItem';
import { theme } from 'components/Theme/main';
import { useKeenSlider } from 'keen-slider/react';

type BannersSliderProps = {
    sliderItems: SliderItem[];
};

const BannersSlider: FC<BannersSliderProps> = (props) => {
    const [loadedImageUrls, setLoadedImageUrls] = useState<{ [key: string]: boolean }>({});
    const [currentSlide, setCurrentSlide] = useState(0);
    const [pause, setPause] = useState(false);
    const timer = useRef<NodeJS.Timer | null>(null);
    const sliderBoxRef = useRef<HTMLDivElement>(null);
    const [sliderRef, slider] = useKeenSlider<HTMLDivElement>({
        loop: true,
        duration: 1000,
        breakpoints: {
            [theme.mediaQueries.queryTablet]: {
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
            if (slider !== null && slider.options().centered) {
                newLoadedImageUrls[Math.min(currentSlide + 1, props.sliderItems.length - 1)] = true;
            }
            return newLoadedImageUrls;
        });
    }, [currentSlide]);
    useEffect(() => {
        if (sliderBoxRef.current !== null) {
            sliderBoxRef.current.addEventListener('mouseover', () => {
                setPause(true);
            });
            sliderBoxRef.current.addEventListener('mouseout', () => {
                setPause(false);
            });
        }
    }, [sliderRef]);
    useEffect(() => {
        timer.current = setInterval(() => {
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
                        imageUrl={getBannersSliderItemImageUrl(sliderItem, loadedImageUrls[index] === true)}
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

export const getBannersSliderItemImageUrl = (sliderItem: SliderItem, isImageLoaded: boolean): string => {
    return isImageLoaded ? sliderItem.images[0]?.sizes[0]?.url || 'images/optimized-noimage.png' : '';
};

export default BannersSlider;
