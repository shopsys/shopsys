import { DEFAULT_PAGE_SIZE } from 'components/Blocks/Pagination/Pagination';
import { encode } from 'js-base64';

export const getEndCursor = (page: number, pageSize: number = DEFAULT_PAGE_SIZE): string => {
    if (page > 1) {
        const endCursor = encode('arrayconnection:' + (page * pageSize - (pageSize + 1)).toString());
        return endCursor;
    }

    return '';
};
