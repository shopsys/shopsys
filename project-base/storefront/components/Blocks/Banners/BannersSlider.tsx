import { Banner } from './Banner';
import { BannersDot } from './BannersDot';
import { bannersReducer } from './bannersUtils';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { TIDs } from 'cypress/tids';
import { TypeSliderItemFragment } from 'graphql/requests/sliderItems/fragments/SliderItemFragment.generated';
import { useEffect, useReducer, useRef } from 'react';
import { useSwipeable } from 'react-swipeable';
import { twJoin } from 'tailwind-merge';
import { isTextSelected } from 'utils/ui/isTextSelected';

const SLIDER_STOP_SLIDE_TIMEOUT = 300 as const;
const SLIDER_SLIDE_DURATION = 500 as const;
const SLIDER_AUTOMATIC_SLIDE_INTERVAL = 5000 as const;

export type BannersSliderProps = {
    sliderItems: TypeSliderItemFragment[];
};

export const BannersSlider: FC<BannersSliderProps> = ({ sliderItems }) => {
    const numItems = sliderItems.length;
    const [bannerSliderState, dispatchBannerSliderStateChange] = useReducer(bannersReducer, {
        sliderPosition: 0,
        isSliding: false,
        slideDirection: 'NEXT',
    });
    const intervalRef = useRef<NodeJS.Timeout | null>(null);

    const slide = (dir: 'PREV' | 'NEXT') => {
        checkAndClearInterval();
        dispatchBannerSliderStateChange({ type: dir, numItems });
        setTimeout(() => {
            dispatchBannerSliderStateChange({ type: 'STOP_SLIDING' });
        }, SLIDER_STOP_SLIDE_TIMEOUT);
        startInterval();
    };

    useEffect(() => {
        const timer = setTimeout(() => {
            startInterval();
        }, SLIDER_AUTOMATIC_SLIDE_INTERVAL);

        return () => {
            clearTimeout(timer);
            checkAndClearInterval();
        };
    }, []);

    const checkAndClearInterval = () => {
        if (intervalRef.current) {
            clearInterval(intervalRef.current);
        }
    };

    const startInterval = () => {
        intervalRef.current = setInterval(() => slide('NEXT'), SLIDER_AUTOMATIC_SLIDE_INTERVAL);
    };

    const moveToSlide = (slideToMoveTo: number) => {
        checkAndClearInterval();
        dispatchBannerSliderStateChange({ type: 'MOVE_TO', slideToMoveTo });
        startInterval();
    };

    const handlers = useSwipeable({
        onSwipedLeft: () => !isTextSelected() && slide('NEXT'),
        onSwipedRight: () => !isTextSelected() && slide('PREV'),
        preventScrollOnSwipe: true,
        onTouchStartOrOnMouseDown: checkAndClearInterval,
        trackMouse: true,
    });

    const slidingButtonsTwClass = twJoin(
        'snap-mandatory vl:grid vl:snap-x vl:auto-cols-[21%] vl:grid-flow-col vl:justify-start vl:overflow-x-auto vl:overscroll-x-contain hide-scrollbar',
    );

    return (
        <div className="flex flex-col" data-tid={TIDs.banners_slider}>
            <div {...handlers}>
                <ExtendedNextLink
                    preventRedirectOnTextSelection
                    className="group block rounded-t-xl rounded-b-none !no-underline select-text"
                    draggable={false}
                    href={sliderItems[bannerSliderState.sliderPosition].link}
                    title={sliderItems[bannerSliderState.sliderPosition].name}
                    onMouseEnter={checkAndClearInterval}
                    onMouseLeave={() => {
                        checkAndClearInterval();
                        startInterval();
                    }}
                >
                    <div className="vl:rounded-b-none w-full overflow-hidden rounded-xl">
                        <div
                            className={twJoin(
                                'flex',
                                sliderItems.length > 1 &&
                                    (!bannerSliderState.isSliding
                                        ? `transform transition-transform motion-safe:translate-x-[calc(-100%)] duration-${SLIDER_SLIDE_DURATION} ease-in-out`
                                        : bannerSliderState.slideDirection === 'PREV'
                                          ? 'translate-x-[calc(2*(-100%))] transform'
                                          : 'translate-x-0 transform'),
                            )}
                        >
                            {sliderItems.map((item, index) => (
                                <Banner
                                    key={item.uuid}
                                    banner={item}
                                    bannerSliderState={bannerSliderState}
                                    index={index}
                                    numItems={numItems}
                                />
                            ))}
                        </div>
                    </div>
                </ExtendedNextLink>
            </div>
            <div
                className={twJoin(
                    'relative',
                    sliderItems.length > 4 &&
                        "vl:after:absolute vl:after:right-0 vl:after:top-0 vl:after:block vl:after:h-full vl:after:w-3 vl:after:bg-background-dark vl:after:bg-linear-to-r/srgb vl:after:from-background-default vl:after:to-transparent vl:after:opacity-25 vl:after:content-['']",
                )}
            >
                <div
                    className={twJoin(
                        'relative mt-3 flex justify-center gap-5 overflow-hidden',
                        'vl:mt-0 vl:gap-0',
                        sliderItems.length > 4 && slidingButtonsTwClass,
                    )}
                >
                    {sliderItems.map((sliderItem, index) => {
                        const isActive = index === bannerSliderState.sliderPosition;

                        return (
                            <BannersDot
                                key={sliderItem.uuid}
                                index={index}
                                isActive={isActive}
                                moveToSlide={moveToSlide}
                                slideInterval={SLIDER_AUTOMATIC_SLIDE_INTERVAL}
                                sliderItem={sliderItem}
                                totalItems={sliderItems.length}
                            />
                        );
                    })}
                </div>
            </div>
        </div>
    );
};
