import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import { createEmptyArray } from 'helpers/arrayUtils';
import Skeleton from 'react-loading-skeleton';

export const SkeletonPageProductDetailMainVariant: FC = () => (
    <Webline>
        <SkeletonModuleBreadcrumbs count={3} />

        <div className="flex flex-col gap-y-6 vl:flex-row">
            <Skeleton className="h-[460px] w-[460px]" containerClassName="flex justify-center vl:order-2 w-full" />

            <div className="flex justify-center gap-2 vl:flex-col vl:justify-start">
                {createEmptyArray(5).map((_, index) => (
                    <Skeleton
                        key={index}
                        className="h-16 w-16 rounded-none"
                        containerClassName="last:hidden vl:last:block"
                    />
                ))}
            </div>
        </div>

        <Skeleton className="mt-8 h-14 w-[460px]" containerClassName="flex vl:order-2 w-full" />

        <div className="mt-8 grid grid-cols-1 gap-2 divide-greyLighter md:grid-cols-2 lg:grid-cols-1 lg:gap-0 lg:divide-y">
            {createEmptyArray(8).map((_, index) => (
                <Skeleton key={index} className="h-96 w-full lg:h-16" containerClassName="p-2" />
            ))}
        </div>

        <Skeleton className="mt-8 h-14 w-full lg:h-8 lg:w-[460px]" containerClassName="flex vl:order-2 w-full" />

        <Skeleton className="mt-4 h-14 w-full lg:mt-12" containerClassName="flex vl:order-2 w-full" />
    </Webline>
);
