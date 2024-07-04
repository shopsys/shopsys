import Skeleton from 'react-loading-skeleton';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

const SkeletonModuleStoreInPopup: FC = () => (
    <div className="flex w-full flex-row items-center gap-3 border-b border-graySlate p-3">
        <Skeleton className="rounded-full h-5 w-5" />
        <Skeleton className="rounded-sm h-12 w-12" />
        <div className="flex flex-1 flex-col text-sm lg:flex-auto lg:basis-full lg:flex-row lg:items-center lg:gap-3">
            <Skeleton className="rounded-sm h-4 w-20" />
            <div>
                <Skeleton className="rounded-sm h-4 w-20" />
                <Skeleton className="rounded-sm h-4 w-60" />
                <Skeleton className="rounded-sm h-4 w-12 my-2" />
                <div className="grid grid-cols-2 w-full mb-2">
                    <Skeleton className="rounded-sm h-4 w-16" />
                    <Skeleton className="rounded-sm h-4 w-36" />
                    <Skeleton className="rounded-sm h-4 w-20" />
                    <Skeleton className="rounded-sm h-4 w-36" />
                    <Skeleton className="rounded-sm h-4 w-12" />
                    <Skeleton className="rounded-sm h-4 w-36" />
                    <Skeleton className="rounded-sm h-4 w-28" />
                    <Skeleton className="rounded-sm h-4 w-24" />
                    <Skeleton className="rounded-sm h-4 w-20" />
                    <Skeleton className="rounded-sm h-4 w-24" />
                    <Skeleton className="rounded-sm h-4 w-16" />
                    <Skeleton className="rounded-sm h-4 w-36" />
                    <Skeleton className="rounded-sm h-4 w-20" />
                    <Skeleton className="rounded-sm h-4 w-24" />
                </div>
            </div>
        </div>
    </div>
);

export const SkeletonModuleTransportStores: FC = () => (
    <div className="flex flex-col">
        {createEmptyArray(2).map((_, index) => (
            <SkeletonModuleStoreInPopup key={index} />
        ))}
    </div>
);
