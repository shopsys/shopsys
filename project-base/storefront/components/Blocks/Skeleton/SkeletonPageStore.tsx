import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';

export const SkeletonPageStore: FC = () => (
    <Webline>
        <SkeletonModuleBreadcrumbs count={3} />

        <div className="flex flex-row items-stretch gap-16">
            <Skeleton className="hidden h-[600px] w-[600px] vl:block" />

            <div className="w-full">
                <div className="mb-12 flex w-full flex-col gap-4 ">
                    <Skeleton className="h-9 w-1/2" />
                    <Skeleton className="mb-3 h-4" count={3} />
                </div>

                <div className="flex">
                    <div className="flex w-full flex-col">
                        <div className="mb-7">
                            <Skeleton className="mb-4 h-6 w-40" />
                            <Skeleton className="mb-2 h-6 w-40 rounded" count={5} />
                        </div>

                        <Skeleton className="h-12 w-full" />
                    </div>
                </div>
            </div>
        </div>

        <Skeleton className="h-48" containerClassName="mt-10 flex justify-between gap-2" count={4} />
    </Webline>
);
