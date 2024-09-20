import { BannerImage } from './BannerImage';
import { BannersDot } from './BannersDot';
import { bannersReducer, getBannerOrderCSSProperty } from './bannersUtils';
import { TIDs } from 'cypress/tids';
import { TypeSliderItemFragment } from 'graphql/requests/sliderItems/fragments/SliderItemFragment.generated';
import { useEffect, useReducer, useRef } from 'react';
import { useSwipeable } from 'react-swipeable';
import { twJoin } from 'tailwind-merge';

const SLIDER_STOP_SLIDE_TIMEOUT = 50 as const;
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
        checkAndClearInterval();
        startInterval();

        return () => {
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
        onSwipedLeft: () => slide('NEXT'),
        onSwipedRight: () => slide('PREV'),
        preventScrollOnSwipe: true,
        onTouchStartOrOnMouseDown: checkAndClearInterval,
        trackMouse: true,
    });

    const slidingButtonsTwClass = twJoin(
        'snap-mandatory vl:grid vl:snap-x vl:auto-cols-[21%] vl:grid-flow-col vl:justify-start vl:overflow-x-auto vl:overscroll-x-contain',
        "vl:[-ms-overflow-style:'none'] vl:[scrollbar-width:'none'] vl:[&::-webkit-scrollbar]:hidden",
        "vl:after:sticky vl:after:right-0 vl:after:top-0 vl:after:block vl:after:h-full vl:after:w-3 vl:after:bg-backgroundDark vl:after:bg-gradient-to-r vl:after:from-background vl:after:to-transparent vl:after:opacity-25 vl:after:content-['']",
    );

    return (
        <div className="flex flex-col" tid={TIDs.banners_slider}>
            <div
                {...handlers}
                onMouseEnter={checkAndClearInterval}
                onMouseLeave={() => {
                    checkAndClearInterval();
                    startInterval();
                }}
            >
                <div className="w-full overflow-hidden rounded-xl vl:rounded-b-none">
                    <div
                        className={twJoin(
                            'flex',
                            !bannerSliderState.isSliding
                                ? `translate-x-[calc(-100%)] transform transition-transform duration-${SLIDER_SLIDE_DURATION} ease-in-out`
                                : bannerSliderState.slideDirection === 'PREV'
                                  ? 'translate-x-[calc(2*(-100%))] transform'
                                  : 'translate-x-0 transform',
                        )}
                    >
                        {sliderItems.map((item, index) => (
                            <div
                                key={index}
                                className={twJoin(
                                    'flex flex-[1_0_100%] basis-full',
                                    getBannerOrderCSSProperty(index, bannerSliderState.sliderPosition, numItems),
                                )}
                            >
                                <BannerImage
                                    desktopAlt={item.webMainImage.name || item.name}
                                    desktopSrc={item.webMainImage.url}
                                    isFirst={index === 0}
                                    mobileAlt={item.mobileMainImage.name || item.name}
                                    mobileSrc={item.mobileMainImage.url}
                                />
                            </div>
                        ))}
                    </div>
                </div>
            </div>
            <div
                className={twJoin(
                    'relative mt-3 flex justify-center gap-5 overflow-hidden',
                    'vl:mt-0 vl:gap-0 vl:rounded-b-md vl:border vl:border-t-0 vl:border-borderAccentLess',
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
                        />
                    );
                })}
            </div>
        </div>
    );
};
