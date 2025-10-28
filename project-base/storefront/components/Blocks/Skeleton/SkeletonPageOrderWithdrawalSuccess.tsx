import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonPageOrderWithdrawalSuccess: FC = () => (
    <Webline>
        <Skeleton className="h-8 md:w-96 lg:h-10" />

        <div className="mt-4 flex flex-col gap-1">
            <Skeleton className="h-6 w-3/4" />
            <Skeleton className="h-6 w-2/4" />
        </div>

        <Skeleton className="my-4 h-32 rounded-xl" />
    </Webline>
);
