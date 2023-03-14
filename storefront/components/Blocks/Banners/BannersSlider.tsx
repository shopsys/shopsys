import { Icon } from 'components/Basic/Icon/Icon';
import { BannersSliderItem } from 'components/Blocks/Banners/BannersSliderItem';
import { desktopFirstSizes, mediaQueries } from 'components/Theme/mediaQueries';
import { SliderItemFragmentApi } from 'graphql/generated';
import { getFirstImageOrNull } from 'helpers/mappers/image';
import { useGetWindowSize } from 'hooks/ui/useGetWindowSize';
import 'keen-slider/keen-slider.min.css';
import { useKeenSlider } from 'keen-slider/react';
import { useEffect, useRef, useState } from 'react';

const DEVICE_BREAKPOINT_SIZE = {
    size: 'tablet',
    query: 'queryTablet',
} as const;

type BannersSliderProps = {
    sliderItems: SliderItemFragmentApi[];
};

export const BannersSlider: FC<BannersSliderProps> = ({ sliderItems, dataTestId }) => {
    const [currentSlide, setCurrentSlide] = useState(0);
    const [pause, setPause] = useState(false);
    const timer = useRef<NodeJS.Timer | null>(null);
    const { width } = useGetWindowSize();
    const sliderBoxRef = useRef<HTMLDivElement>(null);
    const [sliderRef, slider] = useKeenSlider<HTMLDivElement>({
        loop: true,
        duration: 1000,
        breakpoints: {
            [mediaQueries[DEVICE_BREAKPOINT_SIZE.query]]: {
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
        <div className="mb-14 flex flex-col vl:flex-row" ref={sliderBoxRef} data-testid={dataTestId}>
            <div
                ref={sliderRef}
                className="keen-slider lg h-[200px] w-full cursor-pointer lg:h-[250px] vl:h-[290px] vl:w-[calc(100%-307px)]"
            >
                {sliderItems.map((sliderItem, index) => (
                    <BannersSliderItem
                        key={index}
                        image={getFirstImageOrNull(
                            width > desktopFirstSizes[DEVICE_BREAKPOINT_SIZE.size]
                                ? sliderItem.webImages
                                : sliderItem.mobileImages,
                        )}
                        link={sliderItem.link}
                    />
                ))}
            </div>
            <div className="hidden max-h-[307px] max-w-xs pl-6 vl:block">
                {sliderItems.map((sliderItem, index) => (
                    <button
                        className="group relative mb-4 block !w-full cursor-pointer rounded-xl border-2 border-blueLight bg-blueLight py-4 px-8 text-left font-bold transition hover:border-blue hover:bg-blue disabled:border-primary disabled:bg-creamWhite"
                        onClick={() => onMoveToSlideHandler(index)}
                        disabled={index === currentSlide % sliderItems.length}
                        key={sliderItem.uuid}
                    >
                        <Icon
                            iconType="icon"
                            icon="Triangle"
                            width={6}
                            height={6}
                            className="absolute left-3 top-1/2 hidden -translate-y-1/2 text-primary group-disabled:block"
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
                        className="mx-1 h-2 w-3 cursor-pointer rounded-sm border-none bg-greyLight outline-none disabled:bg-primary"
                    />
                ))}
            </div>
        </div>
    );
};
