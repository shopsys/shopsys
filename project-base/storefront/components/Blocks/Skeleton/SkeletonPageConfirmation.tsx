import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonPageConfirmation: FC = () => (
    <Webline>
        <Skeleton className="h-8 md:w-96 lg:h-10" />

        <div className="mt-4 flex flex-col gap-1">
            <Skeleton className="h-6 w-3/4" />
            <Skeleton className="h-6 w-2/4" />
        </div>

        <Skeleton className="my-4 h-32 rounded-xl lg:my-10 lg:h-20" />

        <div className="vl:grid-cols-3 vl:gap-10 grid gap-4">
            <div className="vl:col-span-2 flex flex-col gap-4">
                <Skeleton className="h-44 rounded-xl" />
                <Skeleton className="h-72 rounded-xl" />
            </div>

            <div className="vl:col-span-1">
                <Skeleton className="h-72 rounded-xl" />
            </div>
        </div>
    </Webline>
);
