import { DEFAULT_PAGE_SIZE } from 'components/Blocks/Pagination/Pagination';
import {
    OrderDetailByHashQueryApi,
    OrderDetailQueryApi,
    OrdersQueryApi,
    useOrderDetailByHashQueryApi,
    useOrderDetailQueryApi,
    useOrdersQueryApi,
} from 'graphql/generated';
import { getNewPagination } from 'helpers/pagination/getNewPagination';
import { useQueryError } from 'hooks/graphQl/useQueryError';

export const useOrders = (page: number): OrdersQueryApi['orders'] | undefined => {
    const [{ data, error }] = useOrdersQueryApi({
        variables: { after: getNewPagination(page).endCursor ?? null, first: DEFAULT_PAGE_SIZE },
        requestPolicy: 'cache-and-network',
    });
    useQueryError(error);

    return data?.orders;
};

export const useOrderDetail = (orderNumber: string): OrderDetailQueryApi['order'] | undefined => {
    const [{ data, error }] = useOrderDetailQueryApi({ variables: { orderNumber } });
    useQueryError(error);

    return data?.order;
};

export const useOrderDetailByHash = (urlHash: string): OrderDetailByHashQueryApi['order'] | undefined => {
    const [{ data, error }] = useOrderDetailByHashQueryApi({ variables: { urlHash } });
    useQueryError(error);

    if (data?.order === undefined || data.order === null) {
        return null;
    }

    return data.order;
};
