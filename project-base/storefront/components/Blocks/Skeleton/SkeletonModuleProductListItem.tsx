import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import type { ProductItemProps, ProductListViewModeType } from 'components/Blocks/Product/ProductsList/ProductListItem';
import { twMergeCustom } from 'utils/twMerge';

type SkeletonModuleProductListItemProps = {
    isBasketPopup?: boolean;
    productListViewMode?: ProductListViewModeType;
    size?: ProductItemProps['size'];
    visibleItemsConfig?: ProductItemProps['visibleItemsConfig'];
};

export const SkeletonModuleProductListItem: FC<SkeletonModuleProductListItemProps> = ({
    isBasketPopup,
    productListViewMode = 'grid',
    size,
    visibleItemsConfig,
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
        <SkeletonModuleProductGridListItem
            isBasketPopup={isBasketPopup}
            size={size}
            visibleItemsConfig={visibleItemsConfig}
        />
    );

type SkeletonModuleProductGridListItemProps = Pick<
    SkeletonModuleProductListItemProps,
    'isBasketPopup' | 'size' | 'visibleItemsConfig'
>;

const SkeletonModuleProductGridListItem: FC<SkeletonModuleProductGridListItemProps> = ({
    isBasketPopup,
    size = 'large',
    visibleItemsConfig,
}) => (
    <div
        className={twMergeCustom(
            'flex w-full flex-col gap-2.5 rounded-xl bg-skeleton-less px-2.5 py-5 sm:p-5',
            size === 'medium' && 'pt-10 pb-2.5 sm:pt-10 sm:pb-5',
            isBasketPopup && 'border border-transparent',
        )}
    >
        <Skeleton
            className={twMergeCustom(
                'h-45',
                size === 'extraLarge' && 'h-55',
                size === 'medium' && 'h-35.5',
                size === 'small' && 'h-23.5',
                size === 'extraSmall' && 'h-20',
            )}
        />

        <div
            className={twMergeCustom(
                'flex flex-col gap-1',
                size === 'medium' && 'min-h-15',
                isBasketPopup && 'sm:min-h-10',
            )}
        >
            <Skeleton className="h-4" />
            <Skeleton className={twMergeCustom('h-4', size !== 'medium' && 'w-4/6')} />
            {size === 'medium' && <Skeleton className={twMergeCustom('h-4 w-4/6', isBasketPopup && 'sm:hidden')} />}
        </div>

        {(visibleItemsConfig?.price ?? true) && <Skeleton className="h-7 w-20" />}

        {(visibleItemsConfig?.storeAvailability ?? true) && (
            <div
                className={twMergeCustom(
                    'flex flex-col gap-1',
                    size === 'medium' && 'min-h-15',
                    isBasketPopup && 'sm:min-h-10',
                )}
            >
                <Skeleton className="h-4" />
                <Skeleton className={twMergeCustom('h-4', size !== 'medium' && 'w-4/6')} />
                {size === 'medium' && <Skeleton className={twMergeCustom('h-4 w-4/6', isBasketPopup && 'sm:hidden')} />}
            </div>
        )}

        {(visibleItemsConfig === undefined || visibleItemsConfig.addToCart) && <Skeleton className="h-9 w-full" />}
    </div>
);
