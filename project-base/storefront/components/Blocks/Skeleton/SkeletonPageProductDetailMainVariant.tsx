import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleLastVisitedProducts } from './SkeletonModuleLastVisitedProducts';
import { SkeletonModuleProductSlider } from './SkeletonModuleProductSlider';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export const SkeletonPageProductDetailMainVariant: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={3} />

        <Webline>
            <VerticalStack gap="md">
                <div className="vl:flex-row vl:items-start flex flex-col-reverse items-center gap-y-6">
                    <div className="vl:flex-col flex w-full flex-row gap-3.5 sm:w-auto">
                        {createEmptyArray(5).map((_, index) => (
                            <Skeleton key={index} className="size-16 rounded-lg last:hidden md:last:block" />
                        ))}
                    </div>

                    <div className="flex w-full flex-col items-center justify-center gap-6">
                        <Skeleton className="h-[300px] w-full sm:size-[300px] md:size-[500px]" />
                    </div>
                </div>

                <div>
                    <Skeleton className="mb-1 h-8 w-3/6 lg:h-10" />
                    <Skeleton className="h-4 w-32 rounded-sm" />
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
