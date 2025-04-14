import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonModuleMagazine: FC = () => (
    <Webline width="xxl">
        <Skeleton className="vl:h-[650px] h-[500px] rounded-xl" />
    </Webline>
);
