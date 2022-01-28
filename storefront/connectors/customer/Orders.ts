import { ListedOrdersType, OrderDetailItemType, OrderDetailType } from 'types/orders';
import {
    OrderDetailItemFragmentApi,
    OrderDetailQueryApi,
    OrdersQueryApi,
    useOrderDetailQueryApi,
    useOrdersQueryApi,
} from 'graphql/generated';

import { DomainConfigType } from 'utils/Domain/Domain';
import { initialState } from 'redux/slices/user';
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

    if (apiCustomerOrdersData.orders?.edges !== undefined && apiCustomerOrdersData.orders.edges !== null) {
        for (const edge of apiCustomerOrdersData.orders.edges) {
            if (edge?.node === undefined || edge.node === null) {
                continue;
            }
            mappedOrders.orders.push({
                uuid: edge.node.uuid,
                number: edge.node.number.toString(),
                creationDate: new Date(edge.node.creationDate).toLocaleDateString(currentDomainConfig.defaultLocale),
                items: { quantity: edge.node.items.length - 2 }, // -2 => we need to remove transport and payment
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

export function getOrderDetail(orderNumber: string, currentDomainConfig: DomainConfigType): OrderDetailType | null {
    const [{ data, error }] = useOrderDetailQueryApi({ variables: { orderNumber } });
    useQueryError(error);

    return mapOrderDetailApiData(data?.order, currentDomainConfig);
}

export function mapOrderDetailApiData(
    apiOrderDetailData: OrderDetailQueryApi['order'] | undefined,
    currentDomainConfig: DomainConfigType,
): OrderDetailType | null {
    if (apiOrderDetailData !== null && apiOrderDetailData !== undefined) {
        return {
            ...apiOrderDetailData,
            creationDate: new Date(apiOrderDetailData.creationDate).toLocaleDateString(
                currentDomainConfig.defaultLocale,
            ),
            firstName:
                apiOrderDetailData.firstName !== null && apiOrderDetailData.firstName !== undefined
                    ? apiOrderDetailData.firstName
                    : '',
            lastName:
                apiOrderDetailData.lastName !== null && apiOrderDetailData.lastName !== undefined
                    ? apiOrderDetailData.lastName
                    : '',
            companyName:
                apiOrderDetailData.companyName !== null && apiOrderDetailData.companyName !== undefined
                    ? apiOrderDetailData.companyName
                    : '',
            companyNumber:
                apiOrderDetailData.companyNumber !== null && apiOrderDetailData.companyNumber !== undefined
                    ? apiOrderDetailData.companyNumber
                    : '',
            companyTaxNumber:
                apiOrderDetailData.companyTaxNumber !== null && apiOrderDetailData.companyTaxNumber !== undefined
                    ? apiOrderDetailData.companyTaxNumber
                    : '',
            country: apiOrderDetailData.country.name,
            deliveryFirstName:
                apiOrderDetailData.deliveryFirstName !== null && apiOrderDetailData.deliveryFirstName !== undefined
                    ? apiOrderDetailData.deliveryFirstName
                    : '',
            deliveryLastName:
                apiOrderDetailData.deliveryLastName !== null && apiOrderDetailData.deliveryLastName !== undefined
                    ? apiOrderDetailData.deliveryLastName
                    : '',
            deliveryCompanyName:
                apiOrderDetailData.deliveryCompanyName !== null && apiOrderDetailData.deliveryCompanyName !== undefined
                    ? apiOrderDetailData.deliveryCompanyName
                    : '',
            deliveryTelephone:
                apiOrderDetailData.deliveryTelephone !== null && apiOrderDetailData.deliveryTelephone !== undefined
                    ? apiOrderDetailData.deliveryTelephone
                    : '',
            deliveryStreet:
                apiOrderDetailData.deliveryStreet !== null && apiOrderDetailData.deliveryStreet !== undefined
                    ? apiOrderDetailData.deliveryStreet
                    : '',
            deliveryCity:
                apiOrderDetailData.deliveryCity !== null && apiOrderDetailData.deliveryCity !== undefined
                    ? apiOrderDetailData.deliveryCity
                    : '',
            deliveryPostcode:
                apiOrderDetailData.deliveryPostcode !== null && apiOrderDetailData.deliveryPostcode !== undefined
                    ? apiOrderDetailData.deliveryPostcode
                    : '',
            deliveryCountry:
                apiOrderDetailData.deliveryCountry !== null && apiOrderDetailData.deliveryCountry !== undefined
                    ? apiOrderDetailData.deliveryCountry.name
                    : '',
            note:
                apiOrderDetailData.note !== null && apiOrderDetailData.note !== undefined
                    ? apiOrderDetailData.note
                    : '',
            promoCode:
                apiOrderDetailData.promoCode !== null && apiOrderDetailData.promoCode !== undefined
                    ? apiOrderDetailData.promoCode
                    : '',
            trackingNumber: apiOrderDetailData.trackingNumber !== undefined ? apiOrderDetailData.trackingNumber : null,
            trackingUrl: apiOrderDetailData.trackingUrl !== undefined ? apiOrderDetailData.trackingUrl : null,
            items: mapOrderDetailItems(apiOrderDetailData.items, currentDomainConfig.currencyCode),
        };
    }
    return null;
}

export function mapOrderDetailItems(
    apiOrderDetailItemData: OrderDetailItemFragmentApi[],
    currencyCode: string,
): OrderDetailItemType[] {
    const mappedItems = apiOrderDetailItemData.map((item) => ({
        ...item,
        unitPrice: mapPriceData(item.unitPrice, currencyCode),
        totalPrice: mapPriceData(item.totalPrice, currencyCode),
        unit: item.unit !== null && item.unit !== undefined ? item.unit : '',
    }));

    return mappedItems;
}
