'use client';

import { Pagination } from 'app/_components/Blocks/Pagination/Pagination';
import { useCategoryDetail } from 'components/providers/CategoryDetailProvider';
import { DEFAULT_PAGE_SIZE } from 'config/constants';

type CategoryDetailPaginationProps = {
    hasNextPage: boolean | undefined;
    categoryDetailTotalCount: number;
};
export const CategoryDetailPagination: FC<CategoryDetailPaginationProps> = ({
    categoryDetailTotalCount,
    hasNextPage,
}) => {
    const { paginationScrollTargetRef } = useCategoryDetail();

    return (
        <Pagination
            isWithLoadMore
            hasNextPage={hasNextPage}
            pageSize={DEFAULT_PAGE_SIZE}
            paginationScrollTargetRef={paginationScrollTargetRef}
            totalCount={categoryDetailTotalCount}
        />
    );
};
