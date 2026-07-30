import { TypeSliderItemFragment } from 'graphql/requests/sliderItems/fragments/SliderItemFragment.generated';
import { useEffect, useRef, useState } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';
import { isWholeElementVisible } from 'utils/ui/isWholeElementVisible';
import { useMediaMin } from 'utils/ui/useMediaMin';

type BannersDotProps = {
    index: number;
    isActive: boolean;
    sliderItem: TypeSliderItemFragment;
    moveToSlide: (slideToMoveTo: number) => void;
    moveToSlideWithKeyboard: (slideToMoveTo: number) => void;
    slideInterval: number;
    totalItems: number;
};

export const BannersDot: FC<BannersDotProps> = ({
    index,
    isActive,
    sliderItem,
    moveToSlide,
    moveToSlideWithKeyboard,
    slideInterval,
    totalItems,
}) => {
    const { t } = useTranslation();
    const dotRef = useRef<HTMLButtonElement>(null);
    const [start, setStart] = useState(false);
    const isDesktop = useMediaMin('vl');

    useEffect(() => {
        setStart(true);
    }, []);

    useEffect(() => {
        if (isDesktop && isActive && dotRef.current && isWholeElementVisible(dotRef.current)) {
            dotRef.current.scrollIntoView({
                behavior: 'smooth',
                inline: 'end',
                block: 'nearest',
            });
        }
    }, [isActive, isDesktop]);

    const handleKeyDown = (e: React.KeyboardEvent<HTMLButtonElement>) => {
        if (e.key === 'Enter' || e.key === ' ') {
            moveToSlideWithKeyboard(index);
        }
    };

    return (
        <button
            key={sliderItem.uuid}
            aria-label={t('Go to slide {{ slideName }}', { slideName: sliderItem.name })}
            ref={dotRef}
            tabIndex={0}
            className={twMergeCustom(
                'group relative block size-4 cursor-pointer rounded-full bg-icon-less transition',
                'vl:rounded-none vl:first-of-type:rounded-bl-md vl:last-of-type:rounded-br-md',
                'vl:flex vl:h-auto vl:w-full vl:bg-background-more vl:px-5 vl:py-2 vl:text-left vl:text-text-default',
                'vl:after:absolute vl:after:inset-0 vl:after:border-border-less vl:after:border-t vl:after:border-b vl:after:border-l vl:after:content-[""] vl:first-of-type:after:rounded-bl-md vl:last-of-type:after:rounded-br-md vl:last-of-type:after:border-r',
                isActive && 'bg-text-accent vl:bg-background-default vl:text-text-accent',
                'hover:bg-background-most',
            )}
            onClick={() => moveToSlide(index)}
            onKeyDown={handleKeyDown}
        >
            <span className="h6 wrap-anywhere vl:line-clamp-4 hidden">{sliderItem.name}</span>
            <span
                className={twMergeCustom(
                    'absolute top-0 left-0 z-above vl:block hidden h-0.75 w-0 bg-text-accent transition-all duration-[0s] ease-linear',
                )}
                style={
                    isActive && totalItems > 1 && start
                        ? { transitionDuration: `${slideInterval / 1000}s`, width: '100%' }
                        : undefined
                }
            />
        </button>
    );
};
