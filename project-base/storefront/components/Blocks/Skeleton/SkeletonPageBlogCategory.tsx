import { SkeletonModuleArticleBlog } from './SkeletonModuleArticleBlog';
import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { DEFAULT_BLOG_PAGE_SIZE } from 'config/constants';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export const SkeletonPageBlogCategory: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={2} />

        <div className="flex flex-col gap-6 xl:gap-10">
            <Webline width="xxl">
                <Skeleton className="h-48 rounded-xl" />
            </Webline>

            <Webline>
                <div className="flex flex-col-reverse gap-3 md:gap-10 xl:flex-row xl:gap-[60px]">
                    <div className="flex w-full flex-col gap-4 xl:max-w-[840px] xl:flex-1">
                        <VerticalStack gap="sm">
                            {createEmptyArray(DEFAULT_BLOG_PAGE_SIZE).map((_, index) => (
                                <SkeletonModuleArticleBlog key={index} />
                            ))}
                        </VerticalStack>
                    </div>

                    <Skeleton className="vl:h-[450px] h-11 rounded-xl xl:w-[300px]" />
                </div>
            </Webline>
        </div>
    </>
);
