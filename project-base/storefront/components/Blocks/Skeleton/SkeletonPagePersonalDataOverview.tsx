import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';

export const SkeletonPagePersonalDataOverview: FC = () => (
    <Webline>
        <Skeleton className="w-100 h-36" />
        <Skeleton className="w-100 h-96" />
    </Webline>
);
