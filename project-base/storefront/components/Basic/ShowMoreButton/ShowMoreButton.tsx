import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { TextButton } from 'components/Basic/TextButton/TextButton';
import { ButtonHTMLAttributes } from 'react';
import { twJoin } from 'tailwind-merge';
import { twMergeCustom } from 'utils/twMerge';

type ShowMoreButtonProps = Omit<ButtonHTMLAttributes<HTMLButtonElement>, 'type' | 'aria-expanded' | 'aria-controls'> & {
    isExpanded?: boolean;
    controlsId?: string;
};

/**
 * Text action that expands or collapses more content (items of a list, a long description).
 */
export const ShowMoreButton: FC<ShowMoreButtonProps> = ({
    isExpanded = false,
    controlsId,
    className,
    children,
    ...props
}) => (
    <TextButton
        aria-controls={controlsId}
        aria-expanded={isExpanded}
        className={twMergeCustom('flex min-h-6 w-fit', className)}
        {...props}
    >
        {children}
        <ArrowIcon
            aria-hidden="true"
            className={twJoin('size-4 transition-transform motion-reduce:transition-none', isExpanded && 'rotate-180')}
        />
    </TextButton>
);
