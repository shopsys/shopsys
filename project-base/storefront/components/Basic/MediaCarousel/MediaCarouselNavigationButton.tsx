import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { IconButton, IconButtonProps } from 'components/Forms/Button/IconButton';
import { twMergeCustom } from 'utils/twMerge';

type MediaCarouselNavigationButtonProps = Pick<IconButtonProps, 'className' | 'iconClassName' | 'size' | 'variant'> & {
    direction: 'previous' | 'next';
    title: string;
    onClick: () => void;
    onFocus?: () => void;
};

export const MediaCarouselNavigationButton: FC<MediaCarouselNavigationButtonProps> = ({
    direction,
    title,
    className,
    iconClassName,
    size,
    variant,
    onClick,
    onFocus,
}) => (
    <IconButton
        Icon={ArrowIcon}
        className={className}
        iconClassName={twMergeCustom(direction === 'previous' ? 'rotate-90' : '-rotate-90', iconClassName)}
        size={size}
        title={title}
        variant={variant}
        onClick={(event) => {
            event.stopPropagation();
            onClick();
        }}
        onFocus={onFocus}
    />
);
