import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonModuleBanners: FC = () => (
    <Webline width="xxl">
        <Skeleton className="h-80 vl:h-[457px] rounded-xl" />
    </Webline>
);
