import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';
import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleLastVisitedProducts } from './SkeletonModuleLastVisitedProducts';
import { SkeletonModuleProductSlider } from './SkeletonModuleProductSlider';

export const SkeletonPageProductDetailMainVariant: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={3} />

        <Webline>
            <VerticalStack gap="md">
                <div className="flex flex-col gap-5">
                    <Skeleton className="order-1 vl:order-2 h-8 w-3/6 lg:h-10 xl:mt-3" />

                    <div className="order-2 vl:order-1 flex w-full flex-col items-center gap-6">
                        <div className="flex w-full flex-col items-center justify-center gap-6">
                            <Skeleton className="vl:size-125 h-80 w-full lg:h-125" />
                        </div>

                        <div className="mx-auto flex w-fit max-w-full gap-2 overflow-hidden">
                            {createEmptyArray(6).map((_, index) => (
                                <Skeleton key={index} className="size-12 shrink-0 rounded-lg sm:size-16" />
                            ))}
                        </div>
                    </div>

                    <Skeleton className="order-3 h-4 w-32 rounded-sm" />
                </div>

                <div className="flex flex-col gap-2">
                    {createEmptyArray(5).map((_, index) => (
                        <Skeleton key={index} className="h-20" />
                    ))}
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
