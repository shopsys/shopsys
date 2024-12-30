import Skeleton from 'react-loading-skeleton';
import { twMergeCustom } from 'utils/twMerge';

export const SkeletonModuleCountdown: FC = ({ className }) => (
    <Skeleton className={twMergeCustom('h-[88px] w-80', className)} containerClassName="flex" />
);
