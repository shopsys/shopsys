import { twMergeCustom } from 'utils/twMerge';

export interface SkeletonProps {
    className?: string;
}

export const Skeleton: FC<SkeletonProps> = ({ className }) => {
    return (
        <div
            className={twMergeCustom('custom-loading-skeleton bg-skeleton-default animate-pulse rounded-md', className)}
            role="status"
        />
    );
};
