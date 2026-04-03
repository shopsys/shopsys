import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { TIDs } from 'cypress/tids';
import { TypeSliderItemFragment } from 'graphql/requests/sliderItems/fragments/SliderItemFragment.generated';
import { startTransition, useEffect, useEffectEvent, useReducer, useRef, useState } from 'react';
import { useSwipeable } from 'react-swipeable';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getPageTypeKey } from 'utils/page/getPageTypeKey';
import { getSkeletonTypeFromLink } from 'utils/skeleton/getSkeletonTypeFromLink';
import { isTextSelected } from 'utils/ui/isTextSelected';
import { Banner } from './Banner';
import { BannersDot } from './BannersDot';
import { bannersReducer, getBannerOrderCSSProperty } from './bannersUtils';

const SLIDER_STOP_SLIDE_TIMEOUT = 300 as const;
const SLIDER_AUTOMATIC_SLIDE_INTERVAL = 5000 as const;

type BannersSliderProps = {
    sliderItems: TypeSliderItemFragment[];
};

export const BannersSlider: FC<BannersSliderProps> = ({ sliderItems }) => {
    const { t } = useTranslation();
    const numItems = sliderItems.length;
    const [bannerSliderState, dispatchBannerSliderStateChange] = useReducer(bannersReducer, {
        sliderPosition: 0,
        isSliding: false,
        slideDirection: 'NEXT',
    });
    const [shouldRenderAllSlides, setShouldRenderAllSlides] = useState(false);
    const intervalRef = useRef<NodeJS.Timeout | null>(null);

    const checkAndClearInterval = () => {
        if (intervalRef.current) {
            clearInterval(intervalRef.current);
        }
    };

    const startInterval = () => {
        checkAndClearInterval();
        intervalRef.current = setInterval(() => {
            dispatchBannerSliderStateChange({ type: 'NEXT', numItems: numItems });
            setTimeout(() => {
                startTransition(() => {
                    dispatchBannerSliderStateChange({ type: 'STOP_SLIDING' });
                });
            }, SLIDER_STOP_SLIDE_TIMEOUT);
        }, SLIDER_AUTOMATIC_SLIDE_INTERVAL);
    };

    const slide = (dir: 'PREV' | 'NEXT') => {
        setShouldRenderAllSlides(true);
        checkAndClearInterval();
        dispatchBannerSliderStateChange({ type: dir, numItems });
        setTimeout(() => {
            startTransition(() => {
                dispatchBannerSliderStateChange({ type: 'STOP_SLIDING' });
            });
        }, SLIDER_STOP_SLIDE_TIMEOUT);
        startInterval();
    };

    const onStartInterval = useEffectEvent(() => {
        slide('NEXT');
    });

    const onClearInterval = useEffectEvent(() => {
        checkAndClearInterval();
    });

    useEffect(() => {
        // Delay auto-rotation start so LCP stays locked to slide 1 (which has
        // all the right priority hints). Start earlier if the user interacts first.
        const controller = new AbortController();
        const { signal } = controller;

        const start = () => {
            controller.abort();
            setShouldRenderAllSlides(true);
            onStartInterval();
        };

        const timeout = setTimeout(start, SLIDER_AUTOMATIC_SLIDE_INTERVAL);
        window.addEventListener('scroll', start, { signal, passive: true });
        window.addEventListener('pointerdown', start, { signal });

        return () => {
            clearTimeout(timeout);
            controller.abort();
            onClearInterval();
        };
    }, []);

    const moveToSlide = (slideToMoveTo: number) => {
        setShouldRenderAllSlides(true);
        checkAndClearInterval();
        dispatchBannerSliderStateChange({ type: 'MOVE_TO', slideToMoveTo });
        startInterval();
    };

    const moveToSlideWithKeyboard = (slideToMoveTo: number) => {
        setTimeout(() => {
            const bannerLink = document.getElementById(`banner-link-${slideToMoveTo}`);
            if (bannerLink) {
                bannerLink.focus();
            }
        }, 50);
    };

    const handlers = useSwipeable({
        onSwipedLeft: () => !isTextSelected() && slide('NEXT'),
        onSwipedRight: () => !isTextSelected() && slide('PREV'),
        preventScrollOnSwipe: true,
        onTouchStartOrOnMouseDown: checkAndClearInterval,
        trackMouse: true,
    });

    const slidingButtonsTwClass = twJoin(
        'hide-scrollbar vl:grid vl:snap-x snap-mandatory vl:auto-cols-[21%] vl:grid-flow-col vl:justify-start vl:overflow-x-auto vl:overscroll-x-contain',
    );

    const currentBanner = sliderItems[bannerSliderState.sliderPosition];

    const skeletonType = currentBanner.routeName
        ? getPageTypeKey(currentBanner.routeName)
        : getSkeletonTypeFromLink(currentBanner.link);

    return (
        <div className="flex flex-col" data-tid={TIDs.banners_slider}>
            <div {...handlers}>
                <ExtendedNextLink
                    preventRedirectOnTextSelection
                    className="group no-underline! block select-text rounded-t-xl rounded-b-none"
                    draggable={false}
                    href={currentBanner.link}
                    id={`banner-link-${bannerSliderState.sliderPosition}`}
                    skeletonType={skeletonType}
                    aria-label={t('{{ slideName }}, slide {{ current }} of {{ total }}', {
                        slideName: currentBanner.name,
                        current: bannerSliderState.sliderPosition + 1,
                        total: sliderItems.length,
                    })}
                    onFocus={checkAndClearInterval}
                    onMouseEnter={checkAndClearInterval}
                    onMouseLeave={() => {
                        checkAndClearInterval();
                        if (shouldRenderAllSlides) {
                            startInterval();
                        }
                    }}
                >
                    <div className="w-full overflow-hidden rounded-xl vl:rounded-b-none">
                        <div
                            className={twJoin(
                                'flex',
                                sliderItems.length > 1 &&
                                    (!bannerSliderState.isSliding
                                        ? `translate-x-[calc(-100%)] duration-500 ease-in-out [transition-property:translate] motion-reduce:duration-0`
                                        : bannerSliderState.slideDirection === 'PREV'
                                          ? 'translate-x-[calc(2*(-100%))]'
                                          : 'translate-x-0'),
                            )}
                        >
                            {sliderItems.map((item, index) => {
                                const order = getBannerOrderCSSProperty(
                                    index,
                                    bannerSliderState.sliderPosition,
                                    numItems,
                                );

                                return shouldRenderAllSlides || index === 0 ? (
                                    <Banner key={item.uuid} banner={item} isFirst={index === 0} order={order} />
                                ) : (
                                    <div
                                        key={item.uuid}
                                        className="h-[250px] vl:h-[425px] flex-[1_0_100%] basis-full md:h-[345px]"
                                        style={{ order }}
                                    />
                                );
                            })}
                        </div>
                    </div>
                </ExtendedNextLink>
            </div>
            <div
                className={twJoin(
                    'relative',
                    sliderItems.length > 4 &&
                        "vl:after:absolute vl:after:top-0 vl:after:right-0 vl:after:block vl:after:h-full vl:after:w-3 vl:after:bg-background-dark vl:after:bg-linear-to-r/srgb vl:after:from-background-default vl:after:to-transparent vl:after:opacity-25 vl:after:content-['']",
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
                                moveToSlideWithKeyboard={moveToSlideWithKeyboard}
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
