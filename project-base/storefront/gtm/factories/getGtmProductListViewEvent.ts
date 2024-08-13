import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { mapGtmListedProductType } from 'gtm/mappers/mapGtmListedProductType';
import { GtmProductListViewEventType } from 'gtm/types/events';

export const getGtmProductListViewEvent = (
    products: TypeListedProductFragment[],
    gtmProductListName: GtmProductListNameType,
    currentPageWithLoadMore: number,
    pageSize: number,
    domainUrl: string,
    arePricesHidden: boolean,
): GtmProductListViewEventType => ({
    event: GtmEventType.product_list_view,
    ecommerce: {
        listName: gtmProductListName,
        products: products.map((product, index) => {
            const listedProductIndex = (currentPageWithLoadMore - 1) * pageSize + index;

            return mapGtmListedProductType(product, listedProductIndex, domainUrl);
        }),
        arePricesHidden,
    },
    _clear: true,
});
