import Skeleton from 'react-loading-skeleton';
import { twMergeCustom } from 'utils/twMerge';

export const SkeletonModuleBanners: FC = ({ className }) => (
    <Skeleton className={twMergeCustom('h-80 w-full vl:h-[460px]', className)} />
);
