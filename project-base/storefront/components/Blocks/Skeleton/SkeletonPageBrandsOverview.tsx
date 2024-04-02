import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';
import { twMergeCustom } from 'utils/twMerge';

export const SkeletonPageBrandsOverview: FC = ({ className }) => (
    <Webline>
        <Skeleton className={twMergeCustom('h-[1000px] w-full lg:h-[580px]', className)} />
    </Webline>
);
