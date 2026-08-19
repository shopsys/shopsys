import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';

export const SkeletonPageBlogArticle: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={3} />

        <Webline>
            <div className="grid grid-cols-[minmax(0,52.5rem)] justify-center gap-3 md:gap-10 xl:grid-cols-[minmax(0,52.5rem)_18.75rem] xl:gap-x-5 xl:gap-y-4">
                <Skeleton className="h-10 w-3/5 xl:col-start-1 xl:row-start-1" />

                <Skeleton className="h-10 rounded-xl xl:col-start-2 xl:row-span-2 xl:row-start-1 xl:h-112.5" />

                <div className="flex w-full min-w-0 flex-col gap-4 xl:col-start-1 xl:row-start-2">
                    <VerticalStack gap="sm">
                        <Skeleton className="aspect-32/15 w-full rounded-xl" />

                        <div className="flex flex-wrap gap-2.5">
                            <Skeleton className="h-5 w-20" />
                            <Skeleton className="h-5 w-32" />
                        </div>

                        <Skeleton className="h-10 w-3/5" />
                        <Skeleton className="h-4 w-4/5" />
                        <Skeleton className="h-4" />
                        <Skeleton className="h-4" />
                        <Skeleton className="h-4" />
                        <Skeleton className="h-4" />
                    </VerticalStack>
                </div>
            </div>
        </Webline>
    </>
);
