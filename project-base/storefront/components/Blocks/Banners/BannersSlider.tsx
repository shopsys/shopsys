import { TriangleIcon } from 'components/Basic/Icon/TriangleIcon';
import { BannersSliderItem } from 'components/Blocks/Banners/BannersSliderItem';
import { TIDs } from 'cypress/tids';
import { TypeSliderItemFragment } from 'graphql/requests/sliderItems/fragments/SliderItemFragment.generated';
import 'keen-slider/keen-slider.min.css';
import { useKeenSlider } from 'keen-slider/react';
import { useEffect, useRef, useState } from 'react';
import Skeleton from 'react-loading-skeleton';
import { desktopFirstSizes } from 'utils/mediaQueries';
import { useGetWindowSize } from 'utils/ui/useGetWindowSize';

export type BannersSliderProps = {
    sliderItems: TypeSliderItemFragment[];
};

export const BannersSlider: FC<BannersSliderProps> = ({ sliderItems }) => {
    const [currentSlide, setCurrentSlide] = useState(0);
    const [pause, setPause] = useState(false);
    const timer = useRef<NodeJS.Timeout | null>(null);
    const sliderBoxRef = useRef<HTMLDivElement>(null);

    const { width: windowWidth } = useGetWindowSize();
    const isDesktop = windowWidth > desktopFirstSizes.tablet;
    const isRecognizingWindowWidth = windowWidth < 0;

    const [sliderRef, slider] = useKeenSlider<HTMLDivElement>({
        loop: true,
        duration: 1000,
        slidesPerView: 1,
        autoAdjustSlidesPerView: isRecognizingWindowWidth,
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

        if (sliderBox) {
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
            if (!pause) {
                slider.next();
            }
        }, 5000);
        return () => {
            if (timer.current) {
                clearInterval(timer.current);
            }
        };
    }, [pause, slider]);

    const onMoveToSlideHandler = (newSlideIndex: number) => {
        slider.moveToSlide(slider.details().absoluteSlide - (currentSlide - newSlideIndex));
    };

    return (
        <div className="flex flex-col gap-6 vl:flex-row" ref={sliderBoxRef} tid={TIDs.banners_slider}>
            <div className="keen-slider h-[283px] rounded vl:basis-3/4" ref={sliderRef}>
                {isRecognizingWindowWidth ? (
                    <div className="flex h-full w-full items-center justify-center">
                        <Skeleton className="h-full" containerClassName="h-full w-full" />
                    </div>
                ) : (
                    sliderItems.map((sliderItem, index) => (
                        <BannersSliderItem key={index} isDesktop={isDesktop} isFirst={index === 0} item={sliderItem} />
                    ))
                )}
            </div>
            <div className="flex flex-1 justify-center gap-1 vl:flex-col vl:justify-start vl:gap-4">
                {sliderItems.map((sliderItem, index) => (
                    <button
                        key={sliderItem.uuid}
                        className="group relative block h-2 w-3 cursor-pointer rounded border-none border-graySlate bg-graySlate font-bold outline-none transition active:bg-none disabled:bg-secondary vl:mx-0 vl:h-auto vl:w-full vl:border-2 vl:border-solid vl:border-primary vl:bg-whiteSnow vl:py-4 vl:px-8 vl:text-left vl:hover:border-primaryDark vl:disabled:border-secondary vl:disabled:bg-whiteSnow"
                        disabled={index === currentSlide % sliderItems.length}
                        onClick={() => onMoveToSlideHandler(index)}
                    >
                        <TriangleIcon className="absolute top-1/2 left-3 hidden w-2 -translate-y-1/2 text-primary vl:group-disabled:block" />
                        <span className="hidden vl:inline-block">{sliderItem.name}</span>
                    </button>
                ))}
            </div>
        </div>
    );
};
