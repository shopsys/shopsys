import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';

export const SkeletonPageStore: FC = () => (
    <Webline>
        <SkeletonModuleBreadcrumbs count={3} />

        <div className="flex flex-col w-full lg:flex-row lg:gap-5">
            <div className="w-full lg:basis-1/2">
                <div className="mb-5 lg:flex lg:items-center">
                    <Skeleton className="mb-4 h-8 w-80" />
                </div>

                <Skeleton className="mb-4 h-6 w-60" />
                <Skeleton className="mb-2 h-4 w-40 rounded" count={5} />

                <Skeleton className="mt-8 mb-4 h-6 w-60" />
                <Skeleton className="mb-2 h-6 w-1/2 rounded" count={7} />
            </div>
            <div className="w-full lg:basis-1/2">
                <div className="flex aspect-square w-full mt-5 p-5 bg-backgroundMore rounded-xl lg:mt-0">
                    <Skeleton className="w-full h-full" containerClassName="w-full" />
                </div>
            </div>
        </div>

        <Skeleton className="h-48" containerClassName="mt-10 flex justify-between gap-2" count={4} />
    </Webline>
);
