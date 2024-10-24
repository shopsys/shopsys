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
                <Skeleton className="h-[196px] w-full rounded-xl" />
            </div>
            <div className="mb-[60px] flex flex-col gap-3 md:gap-10 vl:flex-row xl:gap-[116px]">
                <div className="order-2 flex w-full flex-col vl:order-1 vl:flex-1">
                    <div className="mb-16 flex flex-col gap-y-5">
                        {createEmptyArray(DEFAULT_BLOG_PAGE_SIZE).map((_, index) => (
                            <SkeletonModuleArticleBlog key={index} />
                        ))}
                    </div>
                </div>
                <div className="order-1 w-full vl:order-2 vl:w-[300px]">
                    <Skeleton className="h-11 rounded-xl vl:h-[450px] vl:w-[400px]" containerClassName="flex" />
                </div>
            </div>
        </div>
    </Webline>
);
