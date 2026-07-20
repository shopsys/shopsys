import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { twMergeCustom } from 'utils/twMerge';

type MediaCarouselNavigationButtonProps = {
    direction: 'previous' | 'next';
    title: string;
    className?: string;
    iconClassName?: string;
    onClick: () => void;
    onFocus?: () => void;
};

export const MediaCarouselNavigationButton: FC<MediaCarouselNavigationButtonProps> = ({
    direction,
    title,
    className,
    iconClassName,
    onClick,
    onFocus,
}) => (
    <button
        aria-label={title}
        className={twMergeCustom('flex cursor-pointer items-center justify-center rounded-full', className)}
        title={title}
        type="button"
        onClick={(event) => {
            event.stopPropagation();
            onClick();
        }}
        onFocus={onFocus}
    >
        <ArrowIcon
            aria-hidden="true"
            className={twMergeCustom(direction === 'previous' ? 'rotate-90' : '-rotate-90', iconClassName)}
        />
    </button>
);
