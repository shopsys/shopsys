import { getFirstImage } from 'connectors/image/Image';
import { mapPageInfoApiData } from 'connectors/pageInfo/PageInfo';
import { mapPriceData } from 'connectors/price/Prices';
import {
    ListedOrderFragmentApi,
    OrderDetailFragmentApi,
    OrderDetailItemFragmentApi,
    OrderListFragmentApi,
    useOrderDetailByHashQueryApi,
    useOrderDetailQueryApi,
    useOrdersQueryApi,
} from 'graphql/generated';
import { DomainConfigType } from 'helpers/domain/domain';
import { useQueryError } from 'hooks/graphQl/UseQueryError';
import { useShopsysSelector } from 'redux/main';
import { initialState } from 'redux/slices/user';
import { ListedOrderConnectionType, ListedOrderType, OrderDetailItemType, OrderDetailType } from 'types/orders';

export const useOrders = (currentDomainConfig: DomainConfigType): ListedOrderConnectionType | undefined => {
    const { paginationCursor } = useShopsysSelector((state) => state.user.pagination);
    const [{ data, error }] = useOrdersQueryApi({
        variables: { after: paginationCursor, first: initialState.pagination.pageSize },
        requestPolicy: 'cache-and-network',
    });
    useQueryError(error);

    if (data?.orders === undefined || data.orders === null) {
        return undefined;
    }

    return mapOrdersApiData(data.orders, currentDomainConfig);
};

const mapOrdersApiData = (
    apiData: OrderListFragmentApi,
    currentDomainConfig: DomainConfigType,
): ListedOrderConnectionType => {
    return {
        ...apiData,
        pageInfo: mapPageInfoApiData(apiData.pageInfo),
        orders: mapListedOrders(apiData.edges, currentDomainConfig),
    };
};

const mapListedOrders = (
    edges: OrderListFragmentApi['edges'],
    currentDomainConfig: DomainConfigType,
): ListedOrderType[] => {
    if (edges === null) {
        return [];
    }
    const listedOrders = [];
    for (const edge of edges) {
        if (edge?.node === undefined || edge.node === null) {
            continue;
        }
        listedOrders.push(mapListedOrder(edge.node, currentDomainConfig));
    }

    return listedOrders;
};

const mapListedOrder = (apiOrder: ListedOrderFragmentApi, currentDomainConfig: DomainConfigType): ListedOrderType => {
    return {
        ...apiOrder,
        number: apiOrder.number.toString(),
        creationDate: new Date(apiOrder.creationDate).toLocaleDateString(currentDomainConfig.defaultLocale),
        items: { quantity: apiOrder.items.length - 2 }, // -2 => we need to remove transport and payment
        transport: {
            name: apiOrder.transport.name,
            image: getFirstImage(apiOrder.transport.images),
        },
        payment: apiOrder.payment.name,
        totalPrice: mapPriceData(apiOrder.totalPrice, currentDomainConfig.currencyCode),
    };
};

export const useOrderDetail = (orderNumber: string, currentDomainConfig: DomainConfigType): OrderDetailType | null => {
    const [{ data, error }] = useOrderDetailQueryApi({ variables: { orderNumber } });
    useQueryError(error);

    if (data?.order === undefined || data.order === null) {
        return null;
    }

    return mapOrderDetailApiData(data.order, currentDomainConfig);
};

export const useOrderDetailByHash = (
    urlHash: string,
    currentDomainConfig: DomainConfigType,
): OrderDetailType | null => {
    const [{ data, error }] = useOrderDetailByHashQueryApi({ variables: { urlHash } });
    useQueryError(error);

    if (data?.order === undefined || data.order === null) {
        return null;
    }

    return mapOrderDetailApiData(data.order, currentDomainConfig);
};

const mapOrderDetailApiData = (
    apiOrderDetailData: OrderDetailFragmentApi,
    currentDomainConfig: DomainConfigType,
): OrderDetailType | null => {
    return {
        ...apiOrderDetailData,
        creationDate: new Date(apiOrderDetailData.creationDate).toLocaleDateString(currentDomainConfig.defaultLocale),
        firstName: apiOrderDetailData.firstName !== null ? apiOrderDetailData.firstName : '',
        lastName: apiOrderDetailData.lastName !== null ? apiOrderDetailData.lastName : '',
        companyName: apiOrderDetailData.companyName !== null ? apiOrderDetailData.companyName : '',
        companyNumber: apiOrderDetailData.companyNumber !== null ? apiOrderDetailData.companyNumber : '',
        companyTaxNumber: apiOrderDetailData.companyTaxNumber !== null ? apiOrderDetailData.companyTaxNumber : '',
        country: apiOrderDetailData.country.name,
        deliveryFirstName: apiOrderDetailData.deliveryFirstName !== null ? apiOrderDetailData.deliveryFirstName : '',
        deliveryLastName: apiOrderDetailData.deliveryLastName !== null ? apiOrderDetailData.deliveryLastName : '',
        deliveryCompanyName:
            apiOrderDetailData.deliveryCompanyName !== null ? apiOrderDetailData.deliveryCompanyName : '',
        deliveryTelephone: apiOrderDetailData.deliveryTelephone !== null ? apiOrderDetailData.deliveryTelephone : '',
        deliveryStreet: apiOrderDetailData.deliveryStreet !== null ? apiOrderDetailData.deliveryStreet : '',
        deliveryCity: apiOrderDetailData.deliveryCity !== null ? apiOrderDetailData.deliveryCity : '',
        deliveryPostcode: apiOrderDetailData.deliveryPostcode !== null ? apiOrderDetailData.deliveryPostcode : '',
        deliveryCountry: apiOrderDetailData.deliveryCountry !== null ? apiOrderDetailData.deliveryCountry.name : '',
        note: apiOrderDetailData.note !== null ? apiOrderDetailData.note : '',
        promoCode: apiOrderDetailData.promoCode !== null ? apiOrderDetailData.promoCode : '',
        items: mapOrderDetailItems(apiOrderDetailData.items, currentDomainConfig.currencyCode),
    };
};

const mapOrderDetailItems = (
    apiOrderDetailItemData: OrderDetailItemFragmentApi[],
    currencyCode: string,
): OrderDetailItemType[] => {
    const mappedItems = apiOrderDetailItemData.map((item) => ({
        ...item,
        unitPrice: mapPriceData(item.unitPrice, currencyCode),
        totalPrice: mapPriceData(item.totalPrice, currencyCode),
        unit: item.unit !== null ? item.unit : '',
    }));

    return mappedItems;
};
