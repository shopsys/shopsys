import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';

export const SkeletonPageContact: FC = () => (
    <div className="mb-8">
        <Webline>
            <Skeleton className="h-11 w-48 mb-5" />
            <Skeleton className="h-6 w-full" />
            <Skeleton className="h-6 w-2/3 mb-4" />
            <Skeleton className="rounded-xl max-w-3xl w-full h-[440px]" />
        </Webline>
    </div>
);
