import { SkeletonModuleArticleBlog } from './SkeletonModuleArticleBlog';
import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { createEmptyArray } from 'helpers/arrayUtils';
import Skeleton from 'react-loading-skeleton';

export const SkeletonPageBlogCategory: FC = () => (
    <Webline>
        <SkeletonModuleBreadcrumbs count={2} />

        <Skeleton className="mb-3 h-8 w-3/5" />

        <div className="mb-16 flex flex-col gap-8 vl:flex-row">
            <div className="order-2 flex w-full flex-col gap-14 vl:order-1">
                {createEmptyArray(DEFAULT_PAGE_SIZE).map((_, index) => (
                    <SkeletonModuleArticleBlog key={index} />
                ))}
            </div>

            <Skeleton className="h-[450px] vl:w-[400px]" containerClassName="order-1 vl:order-2" />
        </div>
    </Webline>
);
