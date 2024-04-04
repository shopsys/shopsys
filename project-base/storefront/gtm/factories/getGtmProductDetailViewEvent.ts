import { TypeMainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { mapGtmProductDetailType } from 'gtm/mappers/mapGtmProductDetailType';
import { GtmProductDetailViewEventType } from 'gtm/types/events';

export const getGtmProductDetailViewEvent = (
    product: TypeProductDetailFragment | TypeMainVariantDetailFragment,
    currencyCodeCode: string,
    domainUrl: string,
): GtmProductDetailViewEventType => ({
    event: GtmEventType.product_detail_view,
    ecommerce: {
        currencyCode: currencyCodeCode,
        valueWithoutVat: parseFloat(product.price.priceWithoutVat),
        valueWithVat: parseFloat(product.price.priceWithVat),
        products: [mapGtmProductDetailType(product, domainUrl)],
    },
    _clear: true,
});
