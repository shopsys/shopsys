import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { Webline } from 'components/Layout/Webline/Webline';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';
import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';

export const SkeletonPageStores: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={2} />

        <Webline>
            <Skeleton className="mb-4 h-8 w-40 lg:h-10" />

            <div className="flex flex-col-reverse gap-5 lg:flex-row">
                <div className="basis-1/2">
                    <Skeleton className="mb-2.5 h-12" />

                    <div className="flex flex-col gap-2.5">
                        {createEmptyArray(5).map((_, index) => (
                            <div key={index} className="rounded-xl bg-skeleton-less px-5 py-2.5">
                                <div className="flex w-full flex-col justify-between gap-2.5 md:flex-row md:items-center">
                                    <div className="flex flex-col gap-1">
                                        <Skeleton className="h-4.5 w-40 rounded-sm" />
                                        <Skeleton className="h-3 w-60 rounded-sm" />
                                    </div>
                                    <div className="flex items-center gap-1 pr-8 md:flex-col md:items-end">
                                        <Skeleton className="h-6 w-24 rounded-sm" />
                                        <Skeleton className="h-3 w-40 rounded-sm" />
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                <div className="basis-1/2">
                    <div className="flex aspect-square w-full rounded-xl bg-skeleton-less p-5">
                        <Skeleton className="h-full w-full" />
                    </div>
                </div>
            </div>
        </Webline>
    </>
);
