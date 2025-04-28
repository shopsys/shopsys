'use client';

import { Pagination } from 'app/_components/Blocks/Pagination/Pagination';
import { useBlogCategory } from 'components/providers/BlogCategoryProvider';
import { DEFAULT_BLOG_PAGE_SIZE } from 'config/constants';

type BlogCategoryPaginationProps = {
    hasNextPage: boolean | undefined;
    blogCategoryTotalCount: number;
    blogCategoryUuid: string;
};
export const BlogCategoryPagination: FC<BlogCategoryPaginationProps> = ({ blogCategoryTotalCount, hasNextPage }) => {
    const { paginationScrollTargetRef } = useBlogCategory();

    return (
        <Pagination
            isWithLoadMore
            hasNextPage={hasNextPage}
            pageSize={DEFAULT_BLOG_PAGE_SIZE}
            paginationScrollTargetRef={paginationScrollTargetRef}
            totalCount={blogCategoryTotalCount}
            type="blog"
        />
    );
};
