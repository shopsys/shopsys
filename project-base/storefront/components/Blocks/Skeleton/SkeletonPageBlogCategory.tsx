import { SkeletonModuleArticleBlog } from './SkeletonModuleArticleBlog';
import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import { DEFAULT_BLOG_PAGE_SIZE } from 'config/constants';
import Skeleton from 'react-loading-skeleton';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export const SkeletonPageBlogCategory: FC = () => (
    <Webline>
        <SkeletonModuleBreadcrumbs count={2} />
        <div className="scroll-mt-5">
            <div className="mb-6 md:mb-10">
                <Skeleton className="h-48 w-full rounded-xl" />
            </div>
            <div className="vl:flex-row mb-16 flex flex-col gap-3 md:gap-10 xl:gap-28">
                <div className="vl:order-1 vl:flex-1 order-2 flex w-full flex-col">
                    <div className="mb-16 flex flex-col gap-y-5">
                        {createEmptyArray(DEFAULT_BLOG_PAGE_SIZE).map((_, index) => (
                            <SkeletonModuleArticleBlog key={index} />
                        ))}
                    </div>
                </div>
                <div className="vl:order-2 vl:w-[300px] order-1 w-full">
                    <Skeleton className="vl:h-[450px] vl:w-[400px] h-11 rounded-xl" containerClassName="flex" />
                </div>
            </div>
        </div>
    </Webline>
);
