import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

type MediaCarouselPositionCounterProps = {
    itemCount: number;
    selectedIndex: number;
    slideName: string;
    isVisible?: boolean;
    className?: string;
};

export const MediaCarouselPositionCounter: FC<MediaCarouselPositionCounterProps> = ({
    itemCount,
    selectedIndex,
    slideName,
    isVisible = true,
    className,
}) => {
    const { t } = useTranslation();
    const positionLabel = t('{{ slideName }}, slide {{ current }} of {{ total }}', {
        slideName,
        current: selectedIndex + 1,
        total: itemCount,
    });

    return (
        <span
            aria-label={positionLabel}
            aria-live="polite"
            className={twMergeCustom(
                'pointer-events-none rounded-full bg-background-dark/40 px-2 py-1 text-text-inverted text-xs backdrop-blur-xs transition-opacity duration-200 motion-reduce:transition-none',
                isVisible ? 'opacity-100' : 'opacity-0',
                className,
            )}
        >
            <span aria-hidden="true">
                {selectedIndex + 1} / {itemCount}
            </span>
        </span>
    );
};
