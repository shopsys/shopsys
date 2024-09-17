import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export const SkeletonPageStores: FC = () => (
    <Webline>
        <SkeletonModuleBreadcrumbs count={2} />

        <Skeleton className="mb-10 h-8 w-40" />

        <div className="flex w-full flex-col lg:flex-row lg:gap-5">
            <div className="w-full max-lg:order-2 max-lg:mt-5 lg:basis-1/2">
                <div className="flex h-[614px] flex-col gap-2.5 overflow-hidden">
                    {createEmptyArray(5).map((_, index) => (
                        <div key={index} className="rounded-xl bg-backgroundMore px-5 py-2.5">
                            <div className="flex items-center justify-between gap-2.5">
                                <div className="w-full">
                                    <div className="max-vl:mb-2.5">
                                        <Skeleton className="h-6 w-40" />
                                        <Skeleton className="mt-1.5 h-4 w-60" />
                                    </div>
                                    <div className="mt-1.5 flex items-center">
                                        <Skeleton className="h-4 w-10" />
                                        <Skeleton className="ml-2.5 h-4 w-20" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>

            <div className="w-full max-lg:order-1 lg:basis-1/2">
                <div className="mt-5 flex aspect-square w-full rounded-xl bg-backgroundMore p-5 lg:mt-0">
                    <Skeleton className="h-full w-full" containerClassName="w-full" />
                </div>
            </div>
        </div>
    </Webline>
);
