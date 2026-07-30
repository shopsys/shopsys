import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';
import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleComparisonAndWishlistButtons } from './SkeletonModuleComparisonAndWishlistButtons';
import { SkeletonModuleLastVisitedProducts } from './SkeletonModuleLastVisitedProducts';
import { SkeletonModuleProductDetailAddToCart } from './SkeletonModuleProductDetailAddToCart';
import { SkeletonModuleProductDetailSections } from './SkeletonModuleProductDetailSections';

export const SkeletonPageProductDetail: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={3} />

        <VerticalStack gap="md">
            <Webline>
                <div className="flex vl:grid vl:grid-cols-[3fr_2fr] vl:grid-rows-[auto_1fr] flex-col gap-6 vl:gap-y-5">
                    <div className="order-1 vl:col-start-2 vl:row-start-1">
                        <Skeleton className="mb-1 h-6 w-2/6" />
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

                        <div className="flex flex-col gap-1">
                            <Skeleton className="h-4 w-3/4" />
                            <Skeleton className="h-4 w-2/4" />
                        </div>

                        <div className="flex h-56 flex-col gap-4 rounded-xl bg-skeleton-less p-3 sm:p-6">
                            <Skeleton className="h-8 w-20" />
                            <Skeleton className="h-5 w-2/6" />

                            <SkeletonModuleProductDetailAddToCart />

                            <SkeletonModuleComparisonAndWishlistButtons />
                        </div>
                    </div>
                </div>
            </Webline>

            <SkeletonModuleProductDetailSections />

            <Webline>
                <SkeletonModuleLastVisitedProducts />
            </Webline>
        </VerticalStack>
    </>
);
