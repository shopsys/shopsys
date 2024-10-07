import Skeleton from 'react-loading-skeleton';
import { twMergeCustom } from 'utils/twMerge';

export const SkeletonModulePromotedProducts: FC = ({ className }) => (
    <Skeleton className={twMergeCustom('h-[425px] w-full', className)} />
);
