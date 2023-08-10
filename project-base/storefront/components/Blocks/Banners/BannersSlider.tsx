import { Icon } from 'components/Basic/Icon/Icon';
import { BannersSliderItem } from 'components/Blocks/Banners/BannersSliderItem';
import { mediaQueries } from 'components/Theme/mediaQueries';
import { SliderItemFragmentApi } from 'graphql/generated';
import 'keen-slider/keen-slider.min.css';
import { useKeenSlider } from 'keen-slider/react';
import { useEffect, useRef, useState } from 'react';

type BannersSliderProps = {
    sliderItems: SliderItemFragmentApi[];
};

export const BannersSlider: FC<BannersSliderProps> = ({ sliderItems, dataTestId }) => {
    const [currentSlide, setCurrentSlide] = useState(0);
    const [pause, setPause] = useState(false);
    const timer = useRef<NodeJS.Timer | null>(null);
    const sliderBoxRef = useRef<HTMLDivElement>(null);
    const [sliderRef, slider] = useKeenSlider<HTMLDivElement>({
        loop: true,
        duration: 1000,
        breakpoints: {
            [mediaQueries.queryTablet]: {
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
    });

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
        <div className="flex flex-col gap-6 vl:flex-row" ref={sliderBoxRef} data-testid={dataTestId}>
            <div
                ref={sliderRef}
                className="keen-slider h-[200px] w-full cursor-pointer rounded lg:h-[250px] vl:h-[290px] vl:w-[calc(100%-307px)]"
            >
                {sliderItems.map((sliderItem, index) => (
                    <BannersSliderItem key={index} item={sliderItem} />
                ))}
            </div>
            <div className="hidden vl:flex vl:flex-1 vl:flex-col">
                {sliderItems.map((sliderItem, index) => (
                    <button
                        className="group relative mb-4 block !w-full cursor-pointer rounded border-2 border-blueLight bg-blueLight py-4 px-8 text-left font-bold transition hover:border-blue hover:bg-blue disabled:border-primary disabled:bg-creamWhite"
                        onClick={() => onMoveToSlideHandler(index)}
                        disabled={index === currentSlide % sliderItems.length}
                        key={sliderItem.uuid}
                    >
                        <Icon
                            iconType="icon"
                            icon="Triangle"
                            className="absolute left-3 top-1/2 hidden w-2 -translate-y-1/2 text-primary group-disabled:block"
                        />
                        {sliderItem.name}
                    </button>
                ))}
            </div>
            <div className="mt-4 flex justify-center vl:hidden">
                {sliderItems.map((sliderItem, index) => (
                    <button
                        onClick={() => onMoveToSlideHandler(index)}
                        disabled={index === currentSlide % sliderItems.length}
                        key={sliderItem.uuid}
                        className="mx-1 h-2 w-3 cursor-pointer rounded border-none bg-greyLight outline-none disabled:bg-primary"
                    />
                ))}
            </div>
        </div>
    );
};
