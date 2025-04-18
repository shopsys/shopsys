import { Skeleton } from 'components/Basic/Skeleton/Skeleton';

type SkeletonModuleProductListItemProps = {
    isSimpleCard?: boolean;
};

export const SkeletonModuleProductListItem: FC<SkeletonModuleProductListItemProps> = ({ isSimpleCard }) => (
    <div className="bg-skeleton-less flex w-full flex-col gap-2.5 rounded-xl px-2.5 py-5 sm:p-5">
        <Skeleton className="h-[180px]" />

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

        <Skeleton className="h-9" />
    </div>
);
