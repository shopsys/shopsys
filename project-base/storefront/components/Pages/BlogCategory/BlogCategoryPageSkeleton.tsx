import { Webline } from 'components/Layout/Webline/Webline';
import { createEmptyArray } from 'helpers/arrayUtils';
import Skeleton from 'react-loading-skeleton';
import { BlogArticleSkeleton } from '../BlogArticle/BlogArticleSkeleton';
import { DEFAULT_PAGE_SIZE } from 'config/constants';

export const BlogCategoryPageSkeleton: FC = () => (
    <Webline>
        <Skeleton className="mb-5 h-8 w-3/5" />
        <div className="mb-16 flex flex-col gap-8 vl:flex-row">
            <div className="flex w-full flex-col gap-20">
                <div className="flex flex-col justify-center gap-10">
                    {createEmptyArray(DEFAULT_PAGE_SIZE).map((_, index) => (
                        <BlogArticleSkeleton key={index} />
                    ))}
                </div>
            </div>
            <Skeleton className="h-[450px] vl:w-[400px]" />
        </div>
    </Webline>
);
