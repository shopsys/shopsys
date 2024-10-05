import Skeleton from 'react-loading-skeleton';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

const SkeletonModuleStoreInPopup: FC = () => (
    <div className="flex w-full flex-row items-center gap-3 border-b border-borderAccent p-3">
        <Skeleton className="h-5 w-5 rounded-full" />
        <Skeleton className="h-12 w-12 rounded-sm" />
        <div className="flex flex-1 flex-col text-sm lg:flex-auto lg:basis-full lg:flex-row lg:items-center lg:gap-3">
            <Skeleton className="h-4 w-20 rounded-sm" />
            <div>
                <Skeleton className="h-4 w-20 rounded-sm" />
                <Skeleton className="h-4 w-60 rounded-sm" />
                <Skeleton className="my-2 h-4 w-12 rounded-sm" />
                <div className="mb-2 grid w-full grid-cols-2">
                    <Skeleton className="h-4 w-16 rounded-sm" />
                    <Skeleton className="h-4 w-36 rounded-sm" />
                    <Skeleton className="h-4 w-20 rounded-sm" />
                    <Skeleton className="h-4 w-36 rounded-sm" />
                    <Skeleton className="h-4 w-12 rounded-sm" />
                    <Skeleton className="h-4 w-36 rounded-sm" />
                    <Skeleton className="h-4 w-28 rounded-sm" />
                    <Skeleton className="h-4 w-24 rounded-sm" />
                    <Skeleton className="h-4 w-20 rounded-sm" />
                    <Skeleton className="h-4 w-24 rounded-sm" />
                    <Skeleton className="h-4 w-16 rounded-sm" />
                    <Skeleton className="h-4 w-36 rounded-sm" />
                    <Skeleton className="h-4 w-20 rounded-sm" />
                    <Skeleton className="h-4 w-24 rounded-sm" />
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
