import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';
import { twMergeCustom } from 'utils/twMerge';

export const SkeletonModuleBanners: FC = ({ className }) => (
    <Webline width="xxl">
        <Skeleton className={twMergeCustom('vl:h-[460px] h-80 w-full', className)} />
    </Webline>
);
