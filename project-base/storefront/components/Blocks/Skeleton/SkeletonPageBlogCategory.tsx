import { SkeletonModuleArticleBlog } from './SkeletonModuleArticleBlog';
import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import Skeleton from 'react-loading-skeleton';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export const SkeletonPageBlogCategory: FC = () => (
    <Webline>
        <SkeletonModuleBreadcrumbs count={2} />
        <div className="scroll-mt-5">
            <div className="mb-16 flex flex-col vl:flex-row">
                <div className="order-2 mb-16 flex w-full flex-col vl:order-1 vl:flex-1">
                    <Skeleton className="mb-3 h-8 w-3/5" />
                    <div className="mb-16 flex flex-col gap-8">
                        {createEmptyArray(DEFAULT_PAGE_SIZE).map((_, index) => (
                            <SkeletonModuleArticleBlog key={index} />
                        ))}
                    </div>
                </div>
                <div className="order-1 mb-7 flex w-full flex-col vl:order-2 vl:w-5/12 vl:pl-8 xl:w-1/3">
                    <Skeleton className="h-[450px] vl:w-[400px]" containerClassName="order-1 vl:order-2" />
                </div>
            </div>
        </div>
    </Webline>
);
