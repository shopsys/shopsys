import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { Webline } from 'components/Layout/Webline/Webline';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';
import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';

export const SkeletonPageCatalog: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={2} />

        <Webline>
            <Skeleton className="mb-4 h-8 w-40 lg:h-10" />

            <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                {createEmptyArray(8).map((_, index) => (
                    <div key={index} className="flex flex-col">
                        <Skeleton className="aspect-square w-full rounded-xl" />

                        <div className="mt-3 flex flex-col gap-1 pl-2">
                            {createEmptyArray(3).map((_, subIndex) => (
                                <Skeleton key={subIndex} className="h-4 w-3/4 rounded-sm" />
                            ))}
                        </div>
                    </div>
                ))}
            </div>
        </Webline>
    </>
);
