import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { ProductListViewModeType } from 'components/Blocks/Product/ProductsList/ProductListItem';

type SkeletonModuleProductListItemProps = {
    isSimpleCard?: boolean;
    productListViewMode?: ProductListViewModeType;
};

export const SkeletonModuleProductListItem: FC<SkeletonModuleProductListItemProps> = ({
    isSimpleCard,
    productListViewMode = 'grid',
}) =>
    productListViewMode === 'list' ? (
        <div className="grid w-full gap-4 rounded-xl bg-skeleton-less p-3 sm:p-4 md:grid-cols-[minmax(0,1fr)_auto] md:items-center">
            <div className="grid min-w-0 grid-cols-[80px_minmax(0,1fr)] gap-3 sm:gap-4 xl:w-fit xl:grid-cols-[88px_minmax(0,280px)_minmax(0,280px)] xl:items-center">
                <div className="flex justify-center">
                    <Skeleton className="size-20" />
                </div>

                <div className="flex min-w-0 flex-col justify-center gap-2 xl:max-w-70">
                    <Skeleton className="h-5 w-full" />
                    <Skeleton className="h-4 w-4/6" />
                </div>

                <div className="col-span-2 flex flex-col gap-1 xl:col-span-1 xl:max-w-70">
                    <Skeleton className="h-4 w-full" />
                    <Skeleton className="h-4 w-4/6" />
                </div>
            </div>

            <div className="flex min-w-0 flex-col items-end justify-center gap-2">
                <Skeleton className="h-7 w-24" />
                <Skeleton className="h-9 w-40 sm:w-44" />
            </div>
        </div>
    ) : (
        <SkeletonModuleProductGridListItem isSimpleCard={isSimpleCard} />
    );

type SkeletonModuleProductGridListItemProps = {
    isSimpleCard?: boolean;
};

const SkeletonModuleProductGridListItem: FC<SkeletonModuleProductGridListItemProps> = ({ isSimpleCard }) => (
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
