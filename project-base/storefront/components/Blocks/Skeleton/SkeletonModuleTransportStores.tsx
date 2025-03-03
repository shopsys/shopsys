import Skeleton from 'react-loading-skeleton';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

const SkeletonModuleStoreInPopup: FC = () => (
    <div className="border-borderAccent flex w-full flex-row items-center gap-3 border-b p-3">
        <Skeleton className="h-5 w-5 rounded-full" />
        <Skeleton className="h-12 w-12 rounded-xs" />
        <div className="flex flex-1 flex-col text-sm lg:flex-auto lg:basis-full lg:flex-row lg:items-center lg:gap-3">
            <Skeleton className="h-4 w-20 rounded-xs" />
            <div>
                <Skeleton className="h-4 w-20 rounded-xs" />
                <Skeleton className="h-4 w-60 rounded-xs" />
                <Skeleton className="my-2 h-4 w-12 rounded-xs" />
                <div className="mb-2 grid w-full grid-cols-2">
                    <Skeleton className="h-4 w-16 rounded-xs" />
                    <Skeleton className="h-4 w-36 rounded-xs" />
                    <Skeleton className="h-4 w-20 rounded-xs" />
                    <Skeleton className="h-4 w-36 rounded-xs" />
                    <Skeleton className="h-4 w-12 rounded-xs" />
                    <Skeleton className="h-4 w-36 rounded-xs" />
                    <Skeleton className="h-4 w-28 rounded-xs" />
                    <Skeleton className="h-4 w-24 rounded-xs" />
                    <Skeleton className="h-4 w-20 rounded-xs" />
                    <Skeleton className="h-4 w-24 rounded-xs" />
                    <Skeleton className="h-4 w-16 rounded-xs" />
                    <Skeleton className="h-4 w-36 rounded-xs" />
                    <Skeleton className="h-4 w-20 rounded-xs" />
                    <Skeleton className="h-4 w-24 rounded-xs" />
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
