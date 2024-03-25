import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import { createEmptyArray } from 'helpers/arrays/createEmptyArray';
import Skeleton from 'react-loading-skeleton';

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
            <div className="flex w-full flex-col gap-4 lg:order-3 lg:ml-8 vl:ml-10">
                <div className="flex flex-col gap-2">
                    <Skeleton className="h-7 w-5/6" />
                    <Skeleton className="h-4 w-2/6" />
                </div>

                <Skeleton className="h-8 w-20 rounded" />

                <Skeleton className="h-10 w-2/4" />

                <div className="flex flex-col gap-1">
                    <Skeleton className="h-10 w-20" />
                    <Skeleton className="h-5 w-2/6" />
                </div>
                <div className="flex h-[50px] gap-5 vl:h-[54px]">
                    <Skeleton className="h-full w-32" />
                    <Skeleton className="h-full vl:w-[250px]" containerClassName="w-full" />
                </div>
            </div>
        </div>

        <div className="mb-14 mt-10 flex justify-between gap-5 pr-[15%] lg:mt-5 lg:justify-start lg:pr-0">
            {createEmptyArray(2).map((_, index) => (
                <div key={index} className="flex items-center gap-4 lg:flex-col">
                    <Skeleton className="h-11 w-11" />
                    <Skeleton className="h-3 w-16" />
                </div>
            ))}
        </div>

        <div className="border-grayLight mb-6 w-full border-t vl:border-0">
            <Skeleton className="hidden vl:block vl:h-8 vl:w-40 vl:rounded" />

            <div className="flex w-full flex-col vl:flex-row">
                <div className="w-full">
                    <Skeleton className="mb-8 mt-3 h-4 w-16" />
                    <Skeleton className="mb-2 h-5 w-5/6" />
                    <Skeleton className="mb-4 h-40" />
                    <Skeleton className="mb-2 h-5 w-3/6" />
                    <Skeleton className="mb-4 h-24" />
                </div>
            </div>
        </div>
    </Webline>
);
