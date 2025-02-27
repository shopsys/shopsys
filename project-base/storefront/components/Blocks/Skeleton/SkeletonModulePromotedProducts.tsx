import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';
import { twMergeCustom } from 'utils/twMerge';

export const SkeletonModulePromotedProducts: FC = ({ className }) => (
    <Webline>
        <Skeleton className={twMergeCustom('h-[470px] w-full', className)} />
    </Webline>
);
