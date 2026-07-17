import { Skeleton } from 'components/Basic/Skeleton/Skeleton';

type SkeletonModuleProductListItemProps = {
    isSimpleCard?: boolean;
};

export const SkeletonModuleProductListItem: FC<SkeletonModuleProductListItemProps> = ({ isSimpleCard }) => (
    <div className="flex w-full flex-col gap-2.5 rounded-xl bg-skeleton-less px-2.5 py-5 sm:p-5">
        <Skeleton className="h-45" />

        <div className="flex flex-col gap-1">
            <Skeleton className="h-4" />
            <Skeleton className="h-4 w-4/6" />
        </div>

        <Skeleton className="h-7 w-20" />

        {!isSimpleCard && (
            <div className="flex flex-col gap-1">
                <Skeleton className="h-4" />
                <Skeleton className="h-4 w-4/6" />
            </div>
        )}

        {isSimpleCard ? (
            <Skeleton className="h-9" />
        ) : (
            <div className="flex w-full items-center justify-between gap-1">
                <Skeleton className="size-6" />
                <Skeleton className="size-6" />
                <Skeleton className="h-9 w-1/2" />
            </div>
        )}
    </div>
);
