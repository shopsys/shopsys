import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonModuleMagazine: FC = () => (
    <Webline width="xxl">
        <Skeleton className="h-[500px] vl:h-[650px] rounded-xl" />
    </Webline>
);
