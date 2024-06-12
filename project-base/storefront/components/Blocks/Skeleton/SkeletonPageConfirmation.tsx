import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';

export const SkeletonPageConfirmation: FC = () => (
    <Webline>
        <Skeleton className="h-72" containerClassName="h-full w-full" />
    </Webline>
);
