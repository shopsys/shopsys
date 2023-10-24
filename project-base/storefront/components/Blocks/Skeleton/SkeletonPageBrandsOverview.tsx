import { Webline } from 'components/Layout/Webline/Webline';
import { twMergeCustom } from 'helpers/twMerge';
import Skeleton from 'react-loading-skeleton';

export const SkeletonPageBrandsOverview: FC = ({ className }) => (
    <Webline>
        <Skeleton className={twMergeCustom('h-[1000px] w-full lg:h-[580px]', className)} />
    </Webline>
);
