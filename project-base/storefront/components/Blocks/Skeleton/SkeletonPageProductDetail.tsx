import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { twJoin } from 'tailwind-merge';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';
import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleLastVisitedProducts } from './SkeletonModuleLastVisitedProducts';
import { SkeletonModuleProductDetailAddToCart } from './SkeletonModuleProductDetailAddToCart';
import { SkeletonModuleProductDetailSecondaryActions } from './SkeletonModuleProductDetailSecondaryActions';
import { SkeletonModuleProductDetailSections } from './SkeletonModuleProductDetailSections';

type SkeletonPageProductDetailProps = {
    isMainVariant?: boolean;
};

export const SkeletonPageProductDetail: FC<SkeletonPageProductDetailProps> = ({ isMainVariant = false }) => (
    <>
        <SkeletonModuleBreadcrumbs count={3} />

        <VerticalStack gap="md">
            <Webline>
                <div
                    className={twJoin(
                        'flex vl:grid vl:grid-cols-[3fr_2fr] vl:grid-rows-[auto_1fr] flex-col gap-6',
                        isMainVariant ? 'vl:gap-y-2' : 'vl:gap-y-5',
                    )}
                >
                    <div className="order-1 vl:col-start-2 vl:row-start-1">
                        {!isMainVariant && <Skeleton className="mb-1 h-6 w-2/6" />}
                        <Skeleton className="h-8 w-5/6 lg:h-10" />
                    </div>

                    <div className="order-2 vl:col-start-1 vl:row-span-2 vl:row-start-1 flex w-full flex-col items-center gap-6">
                        <div className="flex w-full flex-col items-center justify-center gap-6">
                            <Skeleton className="vl:size-125 h-80 w-full lg:h-125" />
                        </div>

                        <div className="mx-auto flex w-fit max-w-full gap-2 overflow-hidden">
                            {createEmptyArray(6).map((_, index) => (
                                <Skeleton key={index} className="size-12 shrink-0 rounded-lg sm:size-16" />
                            ))}
                        </div>
                    </div>

                    <div className="order-3 vl:col-start-2 vl:row-start-2 flex w-full flex-1 flex-col gap-5">
                        <div className="flex items-center gap-5">
                            <Skeleton className="h-4 w-20 rounded-sm" />
                            <Skeleton className="h-4 w-20 rounded-sm" />
                        </div>

                        {isMainVariant && <Skeleton className="h-5 w-32" />}

                        <div className="flex flex-col gap-1">
                            <Skeleton className="h-4 w-3/4" />
                            <Skeleton className="h-4 w-2/4" />
                        </div>

                        {isMainVariant ? (
                            <div className="flex flex-col gap-4 rounded-xl bg-skeleton-less p-4 sm:p-5">
                                <SkeletonModuleProductDetailAddToCart size="large" />
                                <Skeleton className="h-6 w-40" />
                            </div>
                        ) : (
                            <div className="flex h-56 flex-col gap-4 rounded-xl bg-skeleton-less p-3 sm:p-6">
                                <Skeleton className="h-8 w-20" />
                                <Skeleton className="h-5 w-2/6" />

                                <SkeletonModuleProductDetailAddToCart />

                                <SkeletonModuleProductDetailSecondaryActions />
                            </div>
                        )}
                        {isMainVariant && (
                            <div className="px-4 sm:px-5">
                                <SkeletonModuleProductDetailSecondaryActions />
                            </div>
                        )}
                    </div>
                </div>
            </Webline>

            {isMainVariant && (
                <Webline>
                    <div className="grid grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-1">
                        {createEmptyArray(5).map((_, index) => (
                            <div
                                key={index}
                                className="mx-auto flex w-full max-w-sm flex-col items-center gap-2 rounded-xl bg-skeleton-less p-4 vl:p-5 md:max-w-none lg:grid lg:grid-cols-[4rem_minmax(0,1fr)_minmax(10rem,1fr)_auto] lg:gap-4"
                            >
                                <Skeleton className="h-48 w-full shrink-0 lg:h-16 lg:w-16" />

                                <div className="flex w-full min-w-0 flex-col items-center gap-2 lg:items-start">
                                    <Skeleton className="h-4 w-3/4" />
                                    <Skeleton className="h-4 w-1/2" />
                                </div>

                                <div className="flex min-w-40 flex-col items-center gap-2 lg:items-start">
                                    <Skeleton className="h-4 w-20" />
                                    <Skeleton className="h-4 w-32" />
                                </div>

                                <div className="flex w-full min-w-0 flex-col items-center gap-2 lg:w-auto lg:items-end">
                                    <Skeleton className="h-6 w-24" />
                                    <div className="flex flex-wrap items-center justify-center gap-2 lg:justify-end">
                                        <div className="flex shrink-0 items-center">
                                            <Skeleton className="size-8" />
                                            <Skeleton className="size-8" />
                                        </div>
                                        <Skeleton className="h-9 w-40 shrink-0 sm:w-44" />
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </Webline>
            )}

            <SkeletonModuleProductDetailSections />

            <Webline>
                <SkeletonModuleLastVisitedProducts />
            </Webline>
        </VerticalStack>
    </>
);
