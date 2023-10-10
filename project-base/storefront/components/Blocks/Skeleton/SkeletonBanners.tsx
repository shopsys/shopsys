import { twMergeCustom } from 'helpers/twMerge';
import Skeleton from 'react-loading-skeleton';

export const SkeletonBanners: FC = ({ className }) => (
    <Skeleton className={twMergeCustom('h-80 w-full vl:h-[283px]', className)} />
);
