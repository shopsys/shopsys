import { DEFAULT_PAGE_SIZE } from 'config/constants';

export const calculatePageSize = (currentLoadMore: number, pageSize = DEFAULT_PAGE_SIZE) => {
    return pageSize * (currentLoadMore + 1);
};
