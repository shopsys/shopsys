import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export const SkeletonModuleStoreList: FC = () => (
    <div className="mt-2.5 flex flex-col gap-2.5">
        {createEmptyArray(5).map((_, index) => (
            <div key={index} className="rounded-xl bg-skeleton-less px-5 py-2.5">
                <div className="flex w-full flex-col justify-between gap-1 xl:flex-row">
                    <div className="flex flex-col gap-1">
                        <Skeleton className="h-5 w-40 rounded-sm" />
                        <Skeleton className="h-4 w-60 rounded-sm" />
                        <Skeleton className="h-4 w-32 rounded-sm" />
                    </div>
                    <div className="flex items-center gap-1 pr-8 xl:flex-col xl:items-end">
                        <Skeleton className="h-7 w-28 rounded-sm" />
                        <Skeleton className="h-4 w-44 rounded-sm" />
                    </div>
                </div>
            </div>
        ))}
    </div>
);
