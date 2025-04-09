import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';
import { twMergeCustom } from 'utils/twMerge';

export const SkeletonModulePromotedCategories: FC = ({ className }) => (
    <Webline>
        <Skeleton className={twMergeCustom('vl:h-[635px] h-[245px] w-full', className)} />
    </Webline>
);
