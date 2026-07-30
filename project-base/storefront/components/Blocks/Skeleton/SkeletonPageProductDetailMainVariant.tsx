import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';
import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleLastVisitedProducts } from './SkeletonModuleLastVisitedProducts';
import { SkeletonModuleProductDetailSections } from './SkeletonModuleProductDetailSections';

export const SkeletonPageProductDetailMainVariant: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={3} />

        <VerticalStack gap="md">
            <Webline>
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
            </Webline>

            <Webline>
                <div className="flex flex-col gap-2">
                    {createEmptyArray(5).map((_, index) => (
                        <Skeleton key={index} className="h-20" />
                    ))}
                </div>
            </Webline>

            <SkeletonModuleProductDetailSections />

            <Webline>
                <SkeletonModuleLastVisitedProducts />
            </Webline>
        </VerticalStack>
    </>
);
