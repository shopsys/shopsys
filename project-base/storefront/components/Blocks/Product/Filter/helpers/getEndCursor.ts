import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { encode } from 'js-base64';

export const getEndCursor = (page: number, loadMore = 0, pageSize: number = DEFAULT_PAGE_SIZE): string => {
    if (page > 1 || loadMore > 0) {
        const endCursor = encode('arrayconnection:' + ((page + loadMore) * pageSize - (pageSize + 1)).toString());

        return endCursor;
    }

    return '';
};
