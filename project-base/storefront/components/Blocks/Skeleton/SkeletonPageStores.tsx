import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export const SkeletonPageStores: FC = () => (
    <Webline>
        <SkeletonModuleBreadcrumbs count={2} />

        <Skeleton className="mb-10 h-8 w-40" />

        <div className="flex flex-col w-full lg:flex-row lg:gap-5">
            <div className="w-full lg:basis-1/2 max-lg:order-2 max-lg:mt-5">
                <div className="flex flex-col gap-2.5 h-[614px] overflow-hidden">
                    {createEmptyArray(5).map((_, index) => (
                        <div key={index} className="bg-backgroundMore px-5 py-2.5 rounded-xl">
                            <div className="flex items-center justify-between gap-2.5">
                                <div className="w-full">
                                    <div className="max-vl:mb-2.5">
                                        <Skeleton className="h-6 w-40" />
                                        <Skeleton className="mt-1.5 w-60 h-4" />
                                    </div>
                                    <div className="flex items-center mt-1.5">
                                        <Skeleton className="w-10 h-4" />
                                        <Skeleton className="ml-2.5 w-20 h-4" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>

            <div className="w-full lg:basis-1/2 max-lg:order-1">
                <div className="flex aspect-square w-full mt-5 p-5 bg-backgroundMore rounded-xl lg:mt-0">
                    <Skeleton className="w-full h-full" containerClassName="w-full" />
                </div>
            </div>
        </div>
    </Webline>
);
