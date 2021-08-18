import 'keen-slider/keen-slider.min.css';
import { FC, useEffect, useRef, useState } from 'react';
import {
    StyledBannersSlider,
    StyledBannersSliderBox,
    StyledBannersSliderDotControls,
    StyledBannersSliderItem,
    StyledBannersSliderThumbnailControls,
} from './BannersSlider.style';
import { SliderItem } from 'connectors/sliderItems/types';
import { theme } from 'theme/main';
import { useKeenSlider } from 'keen-slider/react';

type BannersSliderProps = {
    sliderItems: SliderItem[];
};

const BannersSlider: FC<BannersSliderProps> = (props) => {
    const [loadedImageUrls, setLoadedImageUrls] = useState<{ [key: string]: boolean }>({});
    const [currentSlide, setCurrentSlide] = useState(0);
    const [pause, setPause] = useState(false);
    const timer = useRef<NodeJS.Timer | null>(null);
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
        slideChanged(slider) {
            setCurrentSlide(slider.details().relativeSlide);
        },
        dragStart: () => {
            setPause(true);
        },
        dragEnd: () => {
            setPause(false);
        },
    });
    useEffect(() => {
        setLoadedImageUrls((currentLoadedImageUrls) => {
            const newLoadedImageUrls = { ...currentLoadedImageUrls };
            newLoadedImageUrls[currentSlide] = true;
            return newLoadedImageUrls;
        });
    }, [currentSlide]);
    useEffect(() => {
        if (sliderRef.current !== null && sliderRef.current !== undefined) {
            sliderRef.current.addEventListener('mouseover', () => {
                setPause(true);
            });
            sliderRef.current.addEventListener('mouseout', () => {
                setPause(false);
            });
        }
    }, [sliderRef]);
    useEffect(() => {
        timer.current = setInterval(() => {
            if (!pause && slider) {
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
        setCurrentSlide(newSlideIndex);
        slider.moveToSlide(newSlideIndex);
    };

    return (
        <StyledBannersSliderBox>
            <StyledBannersSlider ref={sliderRef} className="keen-slider">
                {props.sliderItems.map((sliderItem, index) => (
                    <a href={sliderItem.link} key={sliderItem.uuid} className="keen-slider__slide">
                        <StyledBannersSliderItem
                            sliderItemImageHeight={sliderItem.images[0].height}
                            sliderItemImageUrl={loadedImageUrls[index] ? sliderItem.images[0].url : ''}
                        />
                    </a>
                ))}
            </StyledBannersSlider>
            <StyledBannersSliderThumbnailControls>
                {props.sliderItems.map((sliderItem, index) => (
                    <button
                        onClick={() => onMoveToSlideHandler(index)}
                        disabled={index === currentSlide}
                        key={sliderItem.uuid}
                    >
                        {sliderItem.name}
                    </button>
                ))}
            </StyledBannersSliderThumbnailControls>
            <StyledBannersSliderDotControls>
                {props.sliderItems.map((sliderItem, index) => (
                    <button
                        onClick={() => onMoveToSlideHandler(index)}
                        disabled={index === currentSlide}
                        key={sliderItem.uuid}
                    />
                ))}
            </StyledBannersSliderDotControls>
        </StyledBannersSliderBox>
    );
};

export default BannersSlider;
