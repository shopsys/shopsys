import { mapGtmProductInterface } from './mapGtmProductInterface';
import { MainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { ProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { GtmProductInterface } from 'gtm/types/objects';

export const mapGtmProductDetailType = (
    product: ProductDetailFragment | MainVariantDetailFragment,
    domainUrl: string,
): GtmProductInterface => mapGtmProductInterface(product, domainUrl);
