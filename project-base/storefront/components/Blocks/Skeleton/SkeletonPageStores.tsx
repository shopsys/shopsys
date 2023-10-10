import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';

export const SkeletonPageStores: FC = () => (
    <Webline>
        <div className="flex">
            <Skeleton className="mb-3 h-8 w-40" containerClassName="mx-auto" />
        </div>

        <Skeleton className="mb-8 h-[500px] w-full" />

        <Skeleton className="mb-10 h-56 w-full" />
    </Webline>
);
