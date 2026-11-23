import { twMergeCustom } from 'utils/twMerge';

type SkeletonProps = {
    className?: string;
};

export const Skeleton: FC<SkeletonProps> = ({ className }) => {
    return (
        <div
            role="status"
            className={twMergeCustom(
                'custom-loading-skeleton rounded-detail bg-skeleton-default motion-safe:animate-pulse',
                className,
            )}
        />
    );
};
