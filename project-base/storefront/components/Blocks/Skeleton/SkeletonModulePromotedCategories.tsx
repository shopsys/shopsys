import { twMergeCustom } from 'helpers/twMerge';
import Skeleton from 'react-loading-skeleton';

export const SkeletonModulePromotedCategories: FC = ({ className }) => (
    <Skeleton className={twMergeCustom('h-28 w-full lg:h-36', className)} />
);
