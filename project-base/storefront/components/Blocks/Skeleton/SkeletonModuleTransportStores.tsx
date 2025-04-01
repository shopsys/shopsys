import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

const SkeletonModuleStoreInPopup: FC = () => (
    <div className="border-border-default flex items-center gap-2 border-b py-4 last:border-b-0">
        <Skeleton className="size-5 rounded-full" />

        <div className="flex flex-col gap-1">
            <Skeleton className="h-5 w-20 rounded-sm" />

            <Skeleton className="h-5 w-40 rounded-sm" />
        </div>
    </div>
);

export const SkeletonModuleTransportStores: FC = () => (
    <div className="flex flex-col">
        {createEmptyArray(5).map((_, index) => (
            <SkeletonModuleStoreInPopup key={index} />
        ))}
    </div>
);
