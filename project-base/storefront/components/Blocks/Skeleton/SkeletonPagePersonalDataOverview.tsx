import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';

export const SkeletonPagePersonalDataOverview: FC = () => (
    <Webline>
        <Skeleton className="mb-8 h-36 w-100" />
        <Skeleton className="h-96 w-100" />
    </Webline>
);
