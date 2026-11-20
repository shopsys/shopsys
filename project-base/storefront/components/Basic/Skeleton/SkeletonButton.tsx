import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { ButtonBaseProps } from 'components/Forms/Button/Button';
import { getButtonClassName } from 'components/Forms/Button/buttonUtils';
import { ReactNode } from 'react';
import { twMergeCustom } from 'utils/twMerge';

type SkeletonButtonProps = Pick<ButtonBaseProps, 'size'> & {
    children: ReactNode;
    className?: string;
    contentClassName?: string;
};

/**
 * Placeholder for a button that takes the button's size from its content.
 * The content (translated label and icons) is laid out invisibly with the button styles,
 * so the placeholder wraps and fills its row like the loaded button.
 */
export const SkeletonButton: FC<SkeletonButtonProps> = ({ size = 'medium', children, className, contentClassName }) => (
    <div className={twMergeCustom('relative', className)}>
        <div
            aria-hidden
            className={twMergeCustom(
                getButtonClassName(undefined, size, false),
                'invisible flex w-full',
                contentClassName,
            )}
        >
            {children}
        </div>

        <Skeleton className="absolute inset-0" />
    </div>
);
