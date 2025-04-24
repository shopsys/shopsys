import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonPageBlogArticle: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={3} />

        <Webline>
            <div className="flex flex-col-reverse gap-3 md:gap-10 xl:flex-row xl:gap-[60px]">
                <div className="flex w-full flex-col gap-4 xl:max-w-[840px] xl:flex-1">
                    <VerticalStack gap="sm">
                        <Skeleton className="h-10 w-3/5" />
                        <Skeleton className="h-96" />
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

                <Skeleton className="h-10 rounded-xl xl:h-[450px] xl:w-[300px]" />
            </div>
        </Webline>
    </>
);
