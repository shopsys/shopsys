import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';

export const SkeletonModuleMagazine: FC = () => (
    <Webline width="xxl">
        <Skeleton className="vl:h-[650px] mb-10 h-[500px] w-full" />
    </Webline>
);
