import Skeleton from 'react-loading-skeleton';
import { twMergeCustom } from 'utils/twMerge';

export const SkeletonModulePromotedCategories: FC = ({ className }) => (
    <Skeleton className={twMergeCustom('h-[150px] w-full lg:h-[200px] vl:h-[285px]', className)} />
);
