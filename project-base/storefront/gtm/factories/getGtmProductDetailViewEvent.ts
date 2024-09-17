import { TypeMainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { mapGtmProductDetailType } from 'gtm/mappers/mapGtmProductDetailType';
import { GtmProductDetailViewEventType } from 'gtm/types/events';
import { getGtmPriceBasedOnVisibility } from 'gtm/utils/getGtmPriceBasedOnVisibility';

export const getGtmProductDetailViewEvent = (
    product: TypeProductDetailFragment | TypeMainVariantDetailFragment,
    currencyCodeCode: string,
    domainUrl: string,
    arePricesHidden: boolean,
): GtmProductDetailViewEventType => ({
    event: GtmEventType.product_detail_view,
    ecommerce: {
        currencyCode: currencyCodeCode,
        valueWithoutVat: getGtmPriceBasedOnVisibility(product.price.priceWithoutVat),
        valueWithVat: getGtmPriceBasedOnVisibility(product.price.priceWithVat),
        products: [mapGtmProductDetailType(product, domainUrl)],
        arePricesHidden,
    },
    _clear: true,
});
