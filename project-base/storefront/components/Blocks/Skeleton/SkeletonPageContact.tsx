import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';

export const SkeletonPageContact: FC = () => (
    <div className="mb-8">
        <Webline>
            <Skeleton className="mb-5 h-11 w-48" />
            <Skeleton className="h-6 w-full" />
            <Skeleton className="mb-4 h-6 w-2/3" />
            <Skeleton className="h-[440px] w-full max-w-3xl rounded-xl" />
        </Webline>
    </div>
);
