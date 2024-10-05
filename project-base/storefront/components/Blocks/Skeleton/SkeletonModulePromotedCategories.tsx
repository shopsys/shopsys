import Skeleton from 'react-loading-skeleton';
import { twMergeCustom } from 'utils/twMerge';

export const SkeletonModulePromotedCategories: FC = ({ className }) => (
    <Skeleton className={twMergeCustom('h-40 w-full lg:h-80', className)} />
);
