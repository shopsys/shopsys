import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonModuleBanners: FC = () => (
    <Webline width="xxl">
        <Skeleton className="vl:h-[457px] h-80 rounded-xl" />
    </Webline>
);
