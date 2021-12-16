import { OrdersQueryApi, useOrdersQueryApi } from 'graphql/generated';

import { DomainConfigType } from 'utils/Domain/Domain';
import { initialState } from 'redux/slices/user';
import { ListedOrdersType } from 'types/orders';
import { mapImageApiData } from 'connectors/image/Image';
import { mapPageInfoApiData } from 'connectors/pageInfo/PageInfo';
import { mapPriceData } from 'connectors/transports/Transports';
import { useQueryError } from 'hooks/graphQl/UseQueryError';
import { useShopsysSelector } from 'redux/main';

export function getOrders(currentDomainConfig: DomainConfigType): ListedOrdersType | undefined {
    const { paginationCursor } = useShopsysSelector((state) => state.user.pagination);
    const [{ data, error }] = useOrdersQueryApi({
        variables: { after: paginationCursor, first: initialState.pagination.pageSize },
    });
    useQueryError(error);

    if (data?.orders === undefined) {
        return undefined;
    }

    return mapOrdersApiData(data, currentDomainConfig);
}

const mapOrdersApiData = (
    apiCustomerOrdersData: OrdersQueryApi,
    currentDomainConfig: DomainConfigType,
): ListedOrdersType => {
    const mappedOrders: ListedOrdersType = {
        ...apiCustomerOrdersData.orders,
        totalCount:
            apiCustomerOrdersData.orders?.totalCount !== undefined ? apiCustomerOrdersData.orders.totalCount : 0,
        pageInfo: mapPageInfoApiData(apiCustomerOrdersData.orders?.pageInfo),
        orders: [],
    };

    if (apiCustomerOrdersData?.orders?.edges !== undefined && apiCustomerOrdersData.orders.edges !== null) {
        for (const edge of apiCustomerOrdersData.orders.edges) {
            if (edge?.node === undefined || edge.node === null) {
                continue;
            }
            mappedOrders.orders.push({
                number: edge.node.number.toString(),
                creationDate: new Date(edge.node.creationDate).toLocaleDateString(currentDomainConfig.defaultLocale),
                items: { quantity: edge.node.items.length - 2 },
                transport: {
                    name: edge.node.transport.name,
                    image: mapImageApiData(edge.node.transport.images),
                },
                payment: edge.node.payment.name,
                totalPrice: mapPriceData(edge.node.totalPrice, currentDomainConfig.currencyCode),
            });
        }
    }

    return { ...mappedOrders };
};
