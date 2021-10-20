import { encode } from 'js-base64';
import { PaginationType } from 'redux/store/UserStore';

const GetNewPagination = (page: number, pageSize = 10): PaginationType => {
    if (page > 1) {
        const endCursor = page * pageSize - (pageSize + 1);
        const encodedCursor = encode('arrayconnection:' + endCursor.toString());
        return { currentPage: page, paginationCursor: encodedCursor };
    }

    return { currentPage: 1, paginationCursor: '' };
};

export default GetNewPagination;
