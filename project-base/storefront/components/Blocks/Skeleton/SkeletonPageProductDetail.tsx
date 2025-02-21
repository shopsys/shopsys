import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export const SkeletonPageProductDetail: FC = () => (
    <Webline>
        <SkeletonModuleBreadcrumbs count={3} />

        <div className="flex flex-col lg:flex-row">
            <Skeleton
                className="mb-2 block h-[335px] max-w-[600px] rounded-none lg:h-[600px] xl:w-[568px]"
                containerClassName="flex justify-center lg:order-2 w-full"
            />
            <div className="mb-8 flex justify-center gap-4 lg:mr-14 lg:flex-col lg:justify-start">
                {createEmptyArray(5).map((_, index) => (
                    <Skeleton
                        key={index}
                        className="h-[70px] w-[70px] rounded-none"
                        containerClassName="last:hidden lg:last:block"
                    />
                ))}
            </div>
            <div className="vl:ml-10 flex w-full flex-col gap-4 lg:order-3 lg:ml-8">
                <div className="flex flex-col gap-2">
                    <Skeleton className="h-7 w-5/6" />
                    <Skeleton className="h-4 w-2/6" />
                </div>

                <Skeleton className="h-8 w-20 rounded-sm" />

                <Skeleton className="h-10 w-2/4" />

                <div className="flex flex-col gap-1">
                    <Skeleton className="h-10 w-20" />
                    <Skeleton className="h-5 w-2/6" />
                </div>
                <div className="vl:h-[54px] flex h-[50px] gap-5">
                    <Skeleton className="h-full w-32" />
                    <Skeleton className="vl:w-[250px] h-full" containerClassName="w-full" />
                </div>
            </div>
        </div>

        <div className="mt-10 mb-14 flex justify-between gap-5 pr-[15%] lg:mt-5 lg:justify-start lg:pr-0">
            {createEmptyArray(2).map((_, index) => (
                <div key={index} className="flex items-center gap-4 lg:flex-col">
                    <Skeleton className="h-11 w-11" />
                    <Skeleton className="h-3 w-16" />
                </div>
            ))}
        </div>

        <div className="border-borderAccent vl:border-0 mb-6 w-full border-t">
            <Skeleton className="vl:block vl:h-8 vl:w-40 vl:rounded-sm hidden" />

            <div className="vl:flex-row flex w-full flex-col">
                <div className="w-full">
                    <Skeleton className="mt-3 mb-8 h-4 w-16" />
                    <Skeleton className="mb-2 h-5 w-5/6" />
                    <Skeleton className="mb-4 h-40" />
                    <Skeleton className="mb-2 h-5 w-3/6" />
                    <Skeleton className="mb-4 h-24" />
                </div>
            </div>
        </div>
    </Webline>
);
