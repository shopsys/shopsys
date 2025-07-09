import { twMergeCustom } from 'utils/twMerge';

export interface SkeletonProps {
    className?: string;
}

export const Skeleton: FC<SkeletonProps> = ({ className }) => {
    return (
        <div
            role="status"
            className={twMergeCustom(
                'custom-loading-skeleton bg-skeleton-default rounded-md motion-safe:animate-pulse',
                className,
            )}
        />
    );
};
