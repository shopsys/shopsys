import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export const SkeletonPageStore: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={3} />

        <Webline>
            <div className="mb-5 flex flex-col gap-2.5 lg:flex-row lg:items-center lg:gap-5">
                <Skeleton className="h-8 w-40 lg:h-10" />
                <Skeleton className="h-5 w-20 rounded-sm" />
            </div>

            <div className="flex w-full flex-col gap-5 lg:flex-row">
                <div className="w-full lg:basis-1/2">
                    <VerticalStack gap="md">
                        <Skeleton className="h-11" />

                        <div className="flex flex-col gap-2">
                            <Skeleton className="h-6 w-40" />
                            <Skeleton className="h-6 w-60" />
                        </div>

                        <div className="flex flex-col gap-2">
                            <Skeleton className="h-6 w-44" />
                            <Skeleton className="h-6 w-40" />
                            <Skeleton className="h-6 w-32" />
                            <Skeleton className="h-6 w-40" />
                        </div>

                        <div className="flex flex-col gap-2">
                            <Skeleton className="h-6" />
                            <Skeleton className="h-6" />
                            <Skeleton className="h-6 w-5/6" />
                            <Skeleton className="h-6 w-4/6" />
                        </div>

                        <div className="flex flex-col gap-2">
                            <Skeleton className="h-6 w-40" />
                            <Skeleton className="h-6 w-60" />
                        </div>

                        <Skeleton className="h-80" />
                    </VerticalStack>
                </div>

                <div className="basis-1/2">
                    <div className="bg-skeleton-less flex aspect-square w-full rounded-xl p-5">
                        <Skeleton className="h-full w-full" />
                    </div>
                </div>
            </div>

            <div className="max-vl:grid-flow-col vl:gap-8 mt-8 grid snap-x snap-mandatory gap-4 overflow-y-hidden overscroll-x-contain max-lg:overflow-x-auto lg:flex lg:flex-wrap">
                {createEmptyArray(3).map((_, index) => (
                    <Skeleton key={index} className="m-0.5 flex h-[190px] w-[280px] rounded-xl" />
                ))}
            </div>
        </Webline>
    </>
);
