import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonModuleBanners: FC = () => (
    <Webline>
        <Skeleton className="h-80 vl:h-114 rounded-xl" />
    </Webline>
);
