import { BannerImage } from './BannerImage';
import { bannersReducer, getBannerOrderCSSProperty } from './bannersUtils';
import { TriangleIcon } from 'components/Basic/Icon/TriangleIcon';
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

    return (
        <div className="flex flex-col gap-6 vl:flex-row" tid={TIDs.banners_slider}>
            <div
                {...handlers}
                className="rounded vl:basis-3/4"
                onMouseEnter={checkAndClearInterval}
                onMouseLeave={() => {
                    checkAndClearInterval();
                    startInterval();
                }}
            >
                <div className="w-full overflow-hidden">
                    <div
                        className={twJoin(
                            'flex',
                            !bannerSliderState.isSliding
                                ? `transform translate-x-[calc(-100%)] transition-transform duration-${SLIDER_SLIDE_DURATION} ease-in-out`
                                : bannerSliderState.slideDirection === 'PREV'
                                  ? 'transform translate-x-[calc(2*(-100%))]'
                                  : 'transform translate-x-0',
                        )}
                    >
                        {sliderItems.map((item, index) => (
                            <div
                                key={index}
                                className={twJoin(
                                    'flex-[1_0_100%] basis-full flex',
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
            <div className="flex flex-1 justify-center gap-1 vl:flex-col vl:justify-start vl:gap-4">
                {sliderItems.map((sliderItem, index) => {
                    const isActive = index === bannerSliderState.sliderPosition;

                    return (
                        <button
                            key={sliderItem.uuid}
                            className={twJoin(
                                'group relative block h-2 w-3 cursor-pointer rounded-full vl:rounded font-bold outline-none transition',
                                'vl:mx-0 vl:h-full vl:px-8 vl:w-full border-8 vl:border-2 vl:border-solid vl:text-left',
                                'border-actionInvertedBorder bg-actionInvertedBackground text-actionInvertedText',
                                'hover:border-actionInvertedBorderHovered hover:bg-actionInvertedBackgroundHovered hover:text-actionInvertedTextHovered',
                                isActive &&
                                    'border-actionInvertedBorderActive bg-actionInvertedBackgroundActive text-actionInvertedTextActive',
                            )}
                            onClick={() => moveToSlide(index)}
                        >
                            <TriangleIcon
                                className={twJoin(
                                    'absolute top-1/2 left-3 hidden w-2 -translate-y-1/2 text-actionInvertedText',
                                    isActive && 'vl:block',
                                )}
                            />
                            <span className="hidden vl:inline-block">{sliderItem.name}</span>
                        </button>
                    );
                })}
            </div>
        </div>
    );
};
