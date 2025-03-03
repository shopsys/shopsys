import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';

export const SkeletonPageConfirmation: FC = () => (
    <Webline>
        <Skeleton className="h-40" containerClassName="h-full w-full" />
        <Skeleton className="my-4 h-32 lg:my-10 lg:h-20" containerClassName="h-full w-full" />
        <div className="vl:grid-cols-3 vl:gap-10 grid gap-4">
            <div className="vl:col-span-2">
                <Skeleton className="h-44" containerClassName="h-full w-full" />
            </div>
            <div className="vl:col-span-1">
                <Skeleton className="h-44" containerClassName="h-full w-full" />
            </div>
        </div>
    </Webline>
);
