import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleComparisonAndWishlistButtons } from './SkeletonModuleComparisonAndWishlistButtons';
import { SkeletonModuleLastVisitedProducts } from './SkeletonModuleLastVisitedProducts';
import { SkeletonModuleProductDetailAddToCart } from './SkeletonModuleProductDetailAddToCart';
import { SkeletonModuleProductSlider } from './SkeletonModuleProductSlider';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export const SkeletonPageProductDetail: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={3} />

        <Webline>
            <VerticalStack gap="md">
                <div className="vl:flex-row flex flex-col gap-6">
                    <div className="vl:basis-3/5 vl:flex-row vl:items-start flex w-full basis-1/2 flex-col-reverse items-center gap-6">
                        <div className="vl:flex-col flex w-full flex-row gap-3.5 sm:w-auto">
                            {createEmptyArray(5).map((_, index) => (
                                <Skeleton key={index} className="size-16 rounded-lg last:hidden md:last:block" />
                            ))}
                        </div>

                        <div className="flex w-full flex-col items-center justify-center gap-6">
                            <Skeleton className="h-[300px] w-full sm:size-[300px] md:size-[500px]" />
                        </div>
                    </div>

                    <div className="flex w-full flex-1 flex-col gap-5">
                        <div>
                            <Skeleton className="mb-1 h-6 w-2/6" />
                            <Skeleton className="h-8 w-5/6 lg:h-10" />
                        </div>

                        <div className="flex items-center gap-5">
                            <Skeleton className="h-4 w-20 rounded-sm" />
                            <Skeleton className="h-4 w-20 rounded-sm" />
                        </div>

                        <div className="flex flex-col gap-1">
                            <Skeleton className="h-4 w-3/4" />
                            <Skeleton className="h-4 w-2/4" />
                        </div>

                        <div className="bg-skeleton-less flex h-56 flex-col gap-4 rounded-xl p-3 sm:p-6">
                            <Skeleton className="h-8 w-20" />
                            <Skeleton className="h-5 w-2/6" />

                            <SkeletonModuleProductDetailAddToCart />

                            <SkeletonModuleComparisonAndWishlistButtons />
                        </div>
                    </div>
                </div>

                <div className="flex flex-col gap-4">
                    <div className="hidden flex-row lg:flex lg:gap-5">
                        <Skeleton className="h-9 w-20 rounded-full" />
                        <Skeleton className="h-9 w-24 rounded-full" />
                        <Skeleton className="h-9 w-28 rounded-full" />
                    </div>

                    <div className="hidden flex-col gap-2 lg:flex">
                        <Skeleton className="mb- h-5" />
                        <Skeleton className="mb- h-5" />
                        <Skeleton className="mb- h-5 w-5/6" />
                        <Skeleton className="mb- h-5 w-4/6" />
                    </div>

                    <Skeleton className="block h-11 lg:hidden" />
                    <Skeleton className="block h-11 lg:hidden" />
                    <Skeleton className="block h-11 lg:hidden" />
                </div>

                <SkeletonModuleProductSlider />

                <SkeletonModuleLastVisitedProducts />
            </VerticalStack>
        </Webline>
    </>
);
