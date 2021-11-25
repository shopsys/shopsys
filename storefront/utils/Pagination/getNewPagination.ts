import { encode } from 'js-base64';
import { PaginationType } from 'redux/slices/user/index';

export const getNewPagination = (page: number, pageSize: number): PaginationType => {
    if (page > 1) {
        const endCursor = page * pageSize - (pageSize + 1);
        const encodedCursor = encode('arrayconnection:' + endCursor.toString());
        return { pageSize, currentPage: page, paginationCursor: encodedCursor };
    }

    return { pageSize, currentPage: 1, paginationCursor: '' };
};
