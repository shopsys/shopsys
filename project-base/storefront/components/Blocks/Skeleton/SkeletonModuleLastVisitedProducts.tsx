import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';

export const SkeletonModuleLastVisitedProducts: FC = () => (
    <Webline className="my-6">
        <Skeleton className="mb-5 h-[438px] w-full" />
    </Webline>
);
